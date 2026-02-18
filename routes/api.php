<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Profile\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);

        Route::get('/{provider}/redirect', [AuthController::class, 'redirectToProvider']);
        Route::get('/{provider}/callback', [AuthController::class, 'handleProviderCallback']);
    });

    Route::prefix('profile')->group(function () {
        Route::middleware(['auth:sanctum'])->group(function () {
            Route::get('/me', [ProfileController::class, 'me']);
            Route::get('/ktp/{filename}', [ProfileController::class, 'showKtp']);
            Route::post('/update', [ProfileController::class, 'update']);
        });
    });
});

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');
