<?php

namespace App\Providers;

use App\Models\User;
use App\Support\BrightshellAccount;
use App\Support\BrightshellSession;
use App\Support\DocAccessResolver;
use App\Support\RoleResolver;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
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

        VerifyEmail::createUrlUsing(function (object $notifiable): string {
            $base = BrightshellAccount::portalBaseUrl();
            URL::forceRootUrl($base);
            try {
                return URL::temporarySignedRoute(
                    'verification.verify',
                    now()->addMinutes((int) config('auth.verification.expire', 60)),
                    [
                        'id' => $notifiable->getKey(),
                        'hash' => sha1($notifiable->getEmailForVerification()),
                    ],
                    absolute: true,
                );
            } finally {
                URL::forceRootUrl((string) config('app.url'));
            }
        });

        VerifyEmail::toMailUsing(function (object $notifiable, string $url): MailMessage {
            return (new MailMessage)
                ->subject('Confirmez votre adresse e-mail — BrightShell')
                ->line('Merci pour votre inscription. Cliquez sur le bouton ci-dessous pour confirmer votre adresse e-mail (vérifiez aussi les courriers indésirables / dossier SPAM).')
                ->action('Confirmer mon adresse e-mail', $url)
                ->line('Si vous n’avez pas créé de compte BrightShell, vous pouvez ignorer ce message.');
        });

        Route::bind('member', function (string $value): User {
            return User::withTrashed()->whereKey($value)->firstOrFail();
        });

        View::composer('layouts.app', function ($view): void {
            $view->with([
                'brightshellLoginUrl' => BrightshellAccount::loginUrl(),
                'brightshellSpaceUrl' => auth()->check()
                    ? RoleResolver::defaultPortalUrl(auth()->user())
                    : BrightshellAccount::defaultSpaceUrl(),
            ]);
        });

        View::composer([
            'portals.docs.dashboard',
            'portals.docs.folder',
            'portals.docs.page',
        ], function ($view): void {
            $user = auth()->user();
            $docNavTree = [];
            $docNavCurrentPath = null;

            if ($user !== null) {
                $docNavTree = app(DocAccessResolver::class)->accessibleNavTree($user);
            }

            if (request()->routeIs('portals.docs.show')) {
                $docNavCurrentPath = request()->route()?->parameter('path');
            }

            $view->with(compact('docNavTree', 'docNavCurrentPath'));
        });
    }
}
