<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ServicesController;
use App\Http\Controllers\RealisationsController;
use App\Http\Controllers\CvController;

// Routes principales
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/services', [ServicesController::class, 'index'])->name('services');
Route::get('/realisations', [RealisationsController::class, 'index'])->name('realisations');
Route::get('/cv', [CvController::class, 'index'])->name('cv');

// Redirections des anciennes URLs .html vers les nouvelles routes propres
Route::permanentRedirect('/index.html', '/');
Route::permanentRedirect('/services.html', '/services');
Route::permanentRedirect('/realisations.html', '/realisations');
Route::permanentRedirect('/cv.html', '/cv');

// Gestion des sous-domaines (cas simple - même appli)
Route::domain('{sub}.brightshell.fr')->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('home.subdomain');
    Route::get('/services', [ServicesController::class, 'index'])->name('services.subdomain');
    Route::get('/realisations', [RealisationsController::class, 'index'])->name('realisations.subdomain');
    Route::get('/cv', [CvController::class, 'index'])->name('cv.subdomain');
});
