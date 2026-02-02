<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TripRequestController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Trip Requests
    Route::apiResource('trip-requests', TripRequestController::class);
    Route::patch('trip-requests/{tripRequest}/status', [TripRequestController::class, 'updateStatus']);
});
