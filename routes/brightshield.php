<?php

use App\Http\Controllers\BrightShield\AccessTokenController;
use App\Http\Controllers\BrightShield\ApproveAuthorizationController;
use App\Http\Controllers\BrightShield\AuthorizationController;
use App\Http\Controllers\BrightShield\JwksController;
use App\Http\Controllers\BrightShield\OpenIdConfigurationController;
use App\Http\Controllers\BrightShield\UserInfoController;
use App\Http\Middleware\EnsureBrightShieldUser;
use Illuminate\Support\Facades\Route;
use Laravel\Passport\Http\Controllers\DenyAuthorizationController;
use Laravel\Passport\Http\Controllers\TransientTokenController;
use Laravel\Passport\Passport;

Route::get('/.well-known/openid-configuration', OpenIdConfigurationController::class)
    ->name('brightshield.oidc.configuration');

Route::prefix('oauth')->group(function (): void {
    Route::post('/token', [AccessTokenController::class, 'issueToken'])
        ->name('passport.token')
        ->middleware('throttle:60,1');

    Route::get('/authorize', [AuthorizationController::class, 'authorize'])
        ->name('passport.authorizations.authorize');

    Route::get('/jwks', JwksController::class)->name('brightshield.jwks');

    Route::get('/userinfo', UserInfoController::class)
        ->middleware(['auth:api', EnsureBrightShieldUser::class])
        ->name('brightshield.userinfo');

    $guard = config('passport.guard', 'web');

    Route::middleware(['web', $guard ? 'auth:'.$guard : 'auth', EnsureBrightShieldUser::class])->group(function (): void {
        Route::post('/token/refresh', [TransientTokenController::class, 'refresh'])
            ->name('passport.token.refresh');

        Route::post('/authorize', [ApproveAuthorizationController::class, 'approve'])
            ->name('passport.authorizations.approve');

        Route::delete('/authorize', [DenyAuthorizationController::class, 'deny'])
            ->name('passport.authorizations.deny');
    });
});
