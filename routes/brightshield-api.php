<?php

/*
|--------------------------------------------------------------------------
| API BrightShield (api.{root}/v1/brightshield/…)
|--------------------------------------------------------------------------
|
| Fichier de routes dissocié par sécurité : uniquement des lectures des
| données utilisateur autorisées via jeton Passport (scopes BrightShield).
| Les jetons Sanctum de l'API privée ne sont pas acceptés ici, et un jeton
| BrightShield ne donne accès à rien d'autre que ces routes.
|
*/

use App\Http\Controllers\BrightShield\ApiMeController;
use Illuminate\Support\Facades\Route;

Route::get('/me', [ApiMeController::class, 'show'])->name('api.brightshield.me');
Route::get('/me/profil', [ApiMeController::class, 'profile'])->name('api.brightshield.me.profile');
Route::get('/me/email', [ApiMeController::class, 'email'])->name('api.brightshield.me.email');
Route::get('/me/telephone', [ApiMeController::class, 'phone'])->name('api.brightshield.me.phone');
Route::get('/me/roles', [ApiMeController::class, 'roles'])->name('api.brightshield.me.roles');
Route::get('/me/compte', [ApiMeController::class, 'account'])->name('api.brightshield.me.account');
