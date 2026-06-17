<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DashboardController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Google Auth routes:
Route::get('/auth/google/redirect', [GoogleAuthController::class, 'redirect'])->name('auth.google.redirect');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');

Route::get('test', function(){
    return view('test');
});


Route::prefix('/api')->group(function () {
    // Auth routes for guest users:
    Route::middleware(['api_guest', 'throttle:5,1'])->group(function () {
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/register', [AuthController::class, 'register']);
    });

    // Routes for email verification:
    Route::middleware(['auth:web'])->group(function(){
        Route::post('/user_to_be_verified', [AuthController::class, 'userToBeVerified']);
        Route::post('/resend_otp', [AuthController::class, 'resendOTP']);
        Route::post('/verify_email', [AuthController::class, 'verifyEmail']);

    });
    
    // Routes for auth users:   
    Route::middleware(['auth:web', 'verified'])->group(function () {
        Route::get('/user', function (Request $request) {
            return $request->user();
        });
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/upload_avatar', [UserController::class, 'uploadAvatar']);
        Route::delete('/delete_avatar', [UserController::class, 'deleteAvatar']);
        Route::patch('/save_profile_details', [UserController::class, 'saveProfileDetails']);
        Route::patch('/change-password', [AuthController::class, 'changePassword']);
        Route::patch('/save-preferences', [UserController::class, 'savePreferences']);

        // Transaction routes:
        Route::post('/store_transaction', [TransactionController::class, 'store'])->name('transaction.store');
        Route::patch('/update_transaction', [TransactionController::class, 'update'])->name('transaction.update');
        Route::get('/transactions', [TransactionController::class, 'index'])->name('transaction.index');
        Route::get('/transaction/{id}', [TransactionController::class, 'show'])->name('transaction.show');
        Route::delete('/transaction/{id}', [TransactionController::class, 'delete'])->name('transaction.delete');

        // Dashboard routes:
        Route::get('/dashboard/stats-summary', [DashboardController::class, 'getSummary'])->name('dashboard.stats');
    });


});

Route::get('/{any?}', function () {
    return view('app');
})->where('any', '.*');

// Route::get('{any}', function () {
//     return view('app');
// })->where('any', '^(?!storage).*$');
