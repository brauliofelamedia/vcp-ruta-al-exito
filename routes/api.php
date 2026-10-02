<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProgressController;
use App\Http\Controllers\Api\RegistrationRequestController;
use App\Http\Controllers\Api\UserProvisionController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    // External user provisioning endpoint (for GoHighLevel, webhooks, admin)
    Route::post('/users', [UserProvisionController::class, 'store']);
    Route::post('/registration-requests', [RegistrationRequestController::class, 'store']);

    Route::middleware('throttle:10,1')->group(function (): void {
        // Passwordless magic link login request
        Route::post('/auth/magic-link', [AuthController::class, 'sendMagicLink']);

        // Public registration disabled (returns 403)
        Route::post('/auth/register', [AuthController::class, 'register']);

        // Password login (retained as backup)
        Route::post('/auth/login', [AuthController::class, 'login']);
    });

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/me/progress', [ProgressController::class, 'show'])->middleware('ability:progress:read');
        Route::post('/me/progress', [ProgressController::class, 'store'])->middleware('ability:progress:write');
    });
});
