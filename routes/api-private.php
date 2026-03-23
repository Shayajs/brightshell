<?php

use App\Http\Controllers\Api\V1\CoursesController;
use App\Http\Controllers\Api\V1\MeController;
use App\Http\Controllers\Api\V1\StudentMaterialsController;
use Illuminate\Support\Facades\Route;

Route::get('/me', [MeController::class, 'show'])->name('api.v1.me.show');
Route::put('/me', [MeController::class, 'update'])->name('api.v1.me.update');

Route::get('/courses', [CoursesController::class, 'index'])->name('api.v1.courses.index');
Route::get('/courses/{studentCourse}', [CoursesController::class, 'show'])->name('api.v1.courses.show');

Route::get('/matieres', [StudentMaterialsController::class, 'index'])->name('api.v1.matieres.index');
Route::get('/matieres/{studentSubject}', [StudentMaterialsController::class, 'show'])->name('api.v1.matieres.show');
Route::get('/fichiers/{file}/markdown', [StudentMaterialsController::class, 'markdown'])->name('api.v1.fichiers.markdown');
Route::get('/fichiers/{file}/telecharger', [StudentMaterialsController::class, 'download'])->name('api.v1.fichiers.download');
