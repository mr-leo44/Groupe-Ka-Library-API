<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class,'register'])->middleware('throttle:5,1');
    Route::post('login', [AuthController::class,'login'])->middleware('throttle:10,1');
    Route::post('social', [AuthController::class,'socialLogin'])->middleware('throttle:10,1');
    Route::middleware('auth:sanctum')->post('logout', [AuthController::class,'logout']);
});