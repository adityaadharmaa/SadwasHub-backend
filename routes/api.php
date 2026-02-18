<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Profile\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // ---- Auth SPACE ----
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);

        Route::get('/{provider}/redirect', [AuthController::class, 'redirectToProvider']);
        Route::get('/{provider}/callback', [AuthController::class, 'handleProviderCallback']);

        Route::middleware(['auth:sanctum'])->group(function () {
            Route::post('/email/verification-notification', [AuthController::class, 'resendVerificationEmail']);
            Route::post('/logout', [AuthController::class, 'logout']);
        });

        Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
            ->middleware(['signed'])
            ->name('verification.verify');

        Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
        Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    });
    // ---- End Auth SPACE ----


    // ------ Admin SPACE ------
    Route::prefix('admin')->group(function () {
        Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
            Route::post('/verify-profile/{profileId}', [ProfileController::class, 'verify']);
        });
    });
    // ------ End Admin Space ------


    // ------ Tenant SPACE ------
    Route::prefix('tenant')->group(function () {
        Route::middleware(['auth:sanctum', 'role:tenant'])->group(function () {
            Route::get('/dashboard', function () {
                return response()->json(['message' => 'Welcome to the tenant dashboard!']);
            });

            Route::middleware(['verified'])->group(function () {
                Route::get('/exclusive-content', function () {
                    return response()->json(['message' => 'This is exclusive content for verified tenants!']);
                });
            });
        });
    });
    // ------ End Tenant Space ------

    // ------ General Profile SPACE ------
    Route::prefix('profile')->group(function () {
        Route::middleware(['auth:sanctum'])->group(function () {
            Route::get('/me', [ProfileController::class, 'me']);
            Route::get('/ktp/{filename}', [ProfileController::class, 'showKtp']);
            Route::post('/update', [ProfileController::class, 'update']);
        });
    });
    // ------ End General Profile Space ------
});

Route::get('/reset-password/{token}', function (string $token) {
    return response()->json(['message' => 'Silakan gunakan token ini di frontend React Anda.', 'token' => $token]);
})->name('password.reset');
