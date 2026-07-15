<?php

use App\Http\Middleware\BlockWebVitrineOnApiHost;
use App\Http\Middleware\BlockWebVitrineOnShieldHost;
use App\Http\Middleware\DeveloperApiCors;
use App\Http\Middleware\EnsureBrightShieldUser;
use App\Http\Middleware\EnsureSanctumApiUser;
use App\Http\Middleware\EnsureUserCanAccessProjectPortal;
use App\Http\Middleware\EnsureUserHasAnyRole;
use App\Http\Middleware\EnsureUserHasDeveloperRole;
use App\Http\Middleware\ForceJsonForApiRequests;
use App\Http\Middleware\PublicApiCors;
use App\Support\AdminEmailVerification;
use App\Support\BrightshellDomain;
use App\Support\RoleResolver;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function (): void {
            /*
            |------------------------------------------------------------------
            | BrightShield (shield.*) — fichier de routes dissocié par sécurité.
            | Seules les routes OAuth2/OIDC répondent sur cet hôte ; tout le
            | reste tombe en 404 JSON (pas de contournement possible).
            |------------------------------------------------------------------
            */
            $shieldHost = BrightshellDomain::effectiveShieldHost();
            if ($shieldHost !== '') {
                // Pas de middleware web global : le token OAuth doit rester sans CSRF.
                // Session web uniquement sur authorize / approve / deny (voir routes/brightshield.php).
                Route::domain($shieldHost)
                    ->group(base_path('routes/brightshield.php'));

                Route::domain($shieldHost)->group(function (): void {
                    Route::fallback(static fn () => response()->json([
                        'message' => 'Cet hôte est réservé à BrightShield (OAuth2 / OIDC).',
                    ], 404))->name('brightshield.fallback');
                });
            }

            $apiHost = trim((string) config('brightshell.domains.api_host', ''));
            $root = BrightshellDomain::effectiveRoot();
            if ($apiHost === '' && $root !== '') {
                $apiHost = 'api.'.$root;
            }
            if ($apiHost === '') {
                return;
            }

            /*
            |------------------------------------------------------------------
            | BrightShield sur l'hôte API : lecture des données autorisées via
            | jeton Passport (scopes) uniquement. Les jetons Sanctum de l'API
            | privée ne donnent pas accès à ces routes, et inversement.
            |------------------------------------------------------------------
            */
            Route::domain($apiHost)
                ->middleware([
                    ForceJsonForApiRequests::class,
                    PublicApiCors::class,
                    'throttle:120,1',
                    'auth:api',
                    EnsureBrightShieldUser::class,
                ])
                ->prefix('v1/brightshield')
                ->group(base_path('routes/brightshield-api.php'));

            Route::domain($apiHost)
                ->middleware([
                    PublicApiCors::class,
                    'throttle:120,1',
                ])
                ->group(base_path('routes/api-public.php'));

            Route::domain($apiHost)
                ->middleware([
                    ForceJsonForApiRequests::class,
                    DeveloperApiCors::class,
                    'throttle:120,1',
                    'auth:sanctum',
                    EnsureSanctumApiUser::class,
                ])
                ->prefix('v1')
                ->group(base_path('routes/api-private.php'));

            Route::domain($apiHost)
                ->middleware([ForceJsonForApiRequests::class])
                ->group(function (): void {
                    Route::fallback(static fn () => response()->json([
                        'message' => 'Route API introuvable.',
                    ], 404))->name('api.fallback');
                });
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
        $middleware->validateCsrfTokens(except: [
            'oauth/token',
            'oauth/token/*',
        ]);
        $middleware->alias([
            'roles.any' => EnsureUserHasAnyRole::class,
            'role.developer' => EnsureUserHasDeveloperRole::class,
            'portal.project' => EnsureUserCanAccessProjectPortal::class,
            'block.web.on.api.host' => BlockWebVitrineOnApiHost::class,
            'block.web.on.shield.host' => BlockWebVitrineOnShieldHost::class,
            'brightshield.user' => EnsureBrightShieldUser::class,
        ]);
        $middleware->redirectGuestsTo(function () {
            $host = request()->getHost();
            $shieldHost = \App\Support\BrightshellDomain::effectiveShieldHost();

            if ($shieldHost !== '' && $host === $shieldHost) {
                return \App\Support\BrightshellAccount::loginUrl();
            }

            return route('login');
        });
        $middleware->redirectUsersTo(function () {
            $user = auth()->user();
            if ($user === null) {
                return url('/');
            }
            AdminEmailVerification::ensureVerifiedIfAdmin($user);
            if ($user instanceof MustVerifyEmail && ! $user->hasVerifiedEmail()) {
                return route('verification.notice');
            }

            return RoleResolver::defaultPortalUrl($user);
        });
    })
    ->withSchedule(function (Schedule $schedule): void {
        $schedule->command('mailbox:process-reverse-verification')->everyFiveMinutes();
        $schedule->command('outbound-api-widgets:sync')->everyFiveMinutes();
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (HttpException $e, Request $request) {
            if (
                $e->getStatusCode() === 419
                && $request->isMethod('post')
                && $request->routeIs('logout')
            ) {
                return response()->view('auth.logout-confirm', status: 419);
            }

            return null;
        });
    })->create();
