<?php

use App\Http\Middleware\DeveloperApiCors;
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
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function (): void {
            $apiHost = trim((string) config('brightshell.domains.api_host', ''));
            $root = BrightshellDomain::effectiveRoot();
            if ($apiHost === '' && $root !== '') {
                $apiHost = 'api.'.$root;
            }
            if ($apiHost === '') {
                return;
            }
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
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
        $middleware->alias([
            'roles.any' => EnsureUserHasAnyRole::class,
            'role.developer' => EnsureUserHasDeveloperRole::class,
            'portal.project' => EnsureUserCanAccessProjectPortal::class,
        ]);
        $middleware->redirectGuestsTo(fn () => route('login'));
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
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
