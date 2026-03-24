<?php

use App\Http\Controllers\Api\V1\AuthTokensController;
use App\Http\Controllers\Api\PublicBusinessProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/v1/entreprise', [PublicBusinessProfileController::class, 'show'])
    ->name('api.public.v1.entreprise');

Route::options('/v1/entreprise', static fn () => response('', 204))
    ->name('api.public.v1.entreprise.options');

Route::post('/v1/auth/token', [AuthTokensController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('api.public.v1.auth.token.store');

Route::options('/v1/auth/token', static fn () => response('', 204))
    ->name('api.public.v1.auth.token.options');
