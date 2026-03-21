<?php

namespace App\Providers;

use App\Support\BrightshellAccount;
use App\Support\BrightshellSession;
use App\Support\RoleResolver;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        BrightshellSession::applySharedCookieDomain();

        View::composer('layouts.app', function ($view): void {
            $view->with([
                'brightshellLoginUrl' => BrightshellAccount::loginUrl(),
                'brightshellSpaceUrl' => auth()->check()
                    ? RoleResolver::defaultPortalUrl(auth()->user())
                    : BrightshellAccount::defaultSpaceUrl(),
            ]);
        });
    }
}
