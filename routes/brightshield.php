<?php

use App\Http\Controllers\BrightShield\AccessTokenController;
use App\Http\Controllers\BrightShield\ApproveAuthorizationController;
use App\Http\Controllers\BrightShield\AuthorizationController;
use App\Http\Controllers\BrightShield\JwksController;
use App\Http\Controllers\BrightShield\OpenIdConfigurationController;
use App\Http\Controllers\BrightShield\UserInfoController;
use App\Http\Middleware\EnsureBrightShieldUser;
use App\Http\Middleware\EnsureBrightShieldWebUser;
use Illuminate\Support\Facades\Route;
use Laravel\Passport\Http\Controllers\DenyAuthorizationController;
use Laravel\Passport\Http\Controllers\TransientTokenController;

/*
|--------------------------------------------------------------------------
| BrightShield — routes OAuth2 / OIDC (hôte shield.* uniquement)
|--------------------------------------------------------------------------
| Les endpoints machine (token, jwks, userinfo, discovery) sont sans session
| web / CSRF. L’écran authorize + approve/deny utilisent la session partagée.
*/

Route::get('/.well-known/openid-configuration', OpenIdConfigurationController::class)
    ->name('brightshield.oidc.configuration');

Route::prefix('oauth')->group(function (): void {
    Route::post('/token', [AccessTokenController::class, 'issueToken'])
        ->middleware('throttle:60,1')
        ->name('passport.token');

    Route::get('/jwks', JwksController::class)->name('brightshield.jwks');

    Route::get('/userinfo', UserInfoController::class)
        ->middleware(['auth:api', EnsureBrightShieldUser::class])
        ->name('brightshield.userinfo');
});

Route::middleware('web')->prefix('oauth')->group(function (): void {
    // Session BrightShell partagée (.{root}) : connecté sur account.* = connecté ici.
    Route::get('/authorize', [AuthorizationController::class, 'authorize'])
        ->middleware(EnsureBrightShieldWebUser::class)
        ->name('passport.authorizations.authorize');

    $guard = config('passport.guard', 'web');

    Route::middleware([$guard ? 'auth:'.$guard : 'auth', EnsureBrightShieldWebUser::class])->group(function (): void {
        Route::post('/token/refresh', [TransientTokenController::class, 'refresh'])
            ->name('passport.token.refresh');

        Route::post('/authorize', [ApproveAuthorizationController::class, 'approve'])
            ->name('passport.authorizations.approve');

        Route::delete('/authorize', [DenyAuthorizationController::class, 'deny'])
            ->name('passport.authorizations.deny');
    });
});
