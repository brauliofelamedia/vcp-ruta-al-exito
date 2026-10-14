<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\MaintenanceToolController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'ruta')->name('ruta');
Route::get('/auth/magic-login', [AuthController::class, 'verifyMagicLink'])->name('auth.magic-login');

// Maintenance & cache clearance for shared hosting / cPanel without terminal
Route::match(['get', 'post'], '/system/optimize', [MaintenanceToolController::class, 'optimize']);
