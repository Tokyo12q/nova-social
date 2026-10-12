<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\HealthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/health', HealthController::class);

    Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
        return $request->user();
    });

    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');
        Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:forgot-password');
        Route::post('/reset-password', [AuthController::class, 'resetPassword']);

        Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
            ->middleware(['signed', 'throttle:6,1'])
            ->name('verification.verify');

        Route::middleware('auth:sanctum')->group(function () {
            Route::get('/me', [AuthController::class, 'me']);
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::post('/email/verification-notification', [AuthController::class, 'resendVerificationEmail'])
                ->middleware('throttle:6,1');
        });
    });
    // Profiles
    Route::get('/profiles/{username}', [\App\Http\Controllers\Api\V1\ProfileController::class, 'show']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::match(['put', 'post'], '/profiles/me', [\App\Http\Controllers\Api\V1\ProfileController::class, 'update']);
        Route::patch('/settings/account', [\App\Http\Controllers\Api\V1\SettingsController::class, 'update']);
        Route::patch('/profiles/me/privacy', [\App\Http\Controllers\Api\V1\SettingsController::class, 'updatePrivacy']);
    });
});

