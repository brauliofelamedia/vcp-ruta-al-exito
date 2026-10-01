<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProgressController;
use App\Http\Controllers\Api\RegistrationRequestController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::middleware('throttle:6,1')->group(function (): void {
        Route::post('/registration-requests', [RegistrationRequestController::class, 'store']);
        Route::post('/auth/register', [AuthController::class, 'register']);
        Route::post('/auth/login', [AuthController::class, 'login']);
    });

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/me/progress', [ProgressController::class, 'show'])->middleware('ability:progress:read');
        Route::post('/me/progress', [ProgressController::class, 'store'])->middleware('ability:progress:write');
    });
});
