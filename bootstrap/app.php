<?php

use App\Http\Middleware\EnsureUserHasAnyRole;
use App\Http\Middleware\PublicApiCors;
use App\Support\BrightshellDomain;
use App\Support\RoleResolver;
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
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
        $middleware->alias([
            'roles.any' => EnsureUserHasAnyRole::class,
        ]);
        $middleware->redirectGuestsTo(fn () => route('login'));
        $middleware->redirectUsersTo(function () {
            $user = auth()->user();

            return $user !== null
                ? RoleResolver::defaultPortalUrl($user)
                : url('/');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
