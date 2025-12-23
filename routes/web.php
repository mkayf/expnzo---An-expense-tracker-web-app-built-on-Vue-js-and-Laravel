<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\GoogleAuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Google Auth routes:
Route::get('/auth/google/redirect', [GoogleAuthController::class, 'redirect'])->name('auth.google.redirect');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');

// Auth routes for guest users:
Route::prefix('/api')->group(function () {
    Route::middleware(['api_guest', 'throttle:5,1'])->group(function () {
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/register', [AuthController::class, 'register']);
    });

    // Routes for auth users:   
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/user', function (Request $request) {
            return $request->user();
        });
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/user_to_be_verified', [AuthController::class, 'userToBeVerified']);
        Route::post('/resend_otp', [AuthController::class, 'resendOTP']);
        Route::post('/verify_email', [AuthController::class, 'verifyEmail']);
    });

});

Route::get('/{any?}', function () {
    return view('app');
})->where('any', '.*');
