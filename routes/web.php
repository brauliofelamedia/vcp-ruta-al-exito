<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'ruta')->name('ruta');
Route::get('/auth/magic-login', [AuthController::class, 'verifyMagicLink'])->name('auth.magic-login');
