<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\MaintenanceToolController;
use App\Http\Controllers\SupervisionController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'ruta')->name('ruta');
Route::get('/auth/magic-login', [AuthController::class, 'verifyMagicLink'])->name('auth.magic-login');

// Maintenance & cache clearance for shared hosting / cPanel without terminal
Route::match(['get', 'post'], '/system/optimize', [MaintenanceToolController::class, 'optimize']);

// Private Supervision & Management Dashboard (Protected by Token)
Route::middleware('supervision')->prefix('supervision')->group(function (): void {
    Route::get('/', [SupervisionController::class, 'index'])->name('supervision.index');
    Route::get('/student/{id}', [SupervisionController::class, 'showStudent'])->name('supervision.student.show');
    Route::get('/magic-link/{id}', [SupervisionController::class, 'generateMagicLink'])->name('supervision.magic-link');
    Route::get('/export', [SupervisionController::class, 'exportCsv'])->name('supervision.export');
});
