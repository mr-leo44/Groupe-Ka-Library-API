<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PasswordController;
use App\Http\Controllers\Api\EmailVerificationController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AuditController;

/*
|--------------------------------------------------------------------------
| Public Authentication Routes
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register'])
        ->middleware('throttle:5,1');
    
    Route::post('login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1');
    
    Route::post('social', [AuthController::class, 'socialLogin'])
        ->middleware('throttle:5,1');
    
    // Password reset
    Route::post('forgot-password', [PasswordController::class, 'sendResetLink'])
        ->middleware('throttle:3,1');
    
    Route::post('reset-password', [PasswordController::class, 'reset'])
        ->middleware('throttle:5,1');
});

/*
|--------------------------------------------------------------------------
| Protected Authentication Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum'])->prefix('auth')->group(function () {
    // Logout
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('logout-all', [AuthController::class, 'logoutAllDevices']);
    
    // Email verification
    Route::post('email/resend', [EmailVerificationController::class, 'resend'])
        ->middleware('throttle:3,1');
    
    Route::get('email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
        ->middleware('signed')
        ->name('verification.verify');
});

/*
|--------------------------------------------------------------------------
| User Profile Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum'])->prefix('user')->group(function () {
    // Profile management
    Route::get('profile', [UserController::class, 'profile']);
    Route::put('profile', [UserController::class, 'update']);
    Route::post('change-password', [UserController::class, 'changePassword']);
    
    // Session management
    Route::get('sessions', [UserController::class, 'sessions']);
    Route::delete('sessions/{tokenId}', [UserController::class, 'revokeSession'])->where('tokenId', '[0-9]+');
});

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin')->group(function () {
    
    // User management
    Route::get('users', [UserController::class, 'index']);
    Route::get('users/{user}', [UserController::class, 'show']);
    Route::put('users/{user}/role', [UserController::class, 'updateRole']);
    Route::delete('users/{user}', [UserController::class, 'destroy']);
    Route::post('users/{id}/restore', [UserController::class, 'restore']);
    
    // Audit logs
    Route::get('audits', [AuditController::class, 'index']);
    Route::get('audits/user/{user}', [AuditController::class, 'userAudits']);
    
    // Activity logs
    Route::get('activities', [AuditController::class, 'activityLogs']);
    Route::get('activities/user/{user}', [AuditController::class, 'userActivityLogs']);
    Route::get('activities/security', [AuditController::class, 'securityEvents']);
    
    // Statistics
    Route::get('statistics', [AuditController::class, 'statistics']);
});

/*
|--------------------------------------------------------------------------
| Example: Protected routes that require verified email
|--------------------------------------------------------------------------
| Uncomment to require email verification for specific routes
*/
// Route::middleware(['auth:sanctum', 'verified'])->group(function () {
//     Route::get('books', [BookController::class, 'index']);
//     Route::post('books/purchase', [BookController::class, 'purchase']);
// });
