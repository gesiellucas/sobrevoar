<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DestinationController;
use App\Http\Controllers\Api\TravelerController;
use App\Http\Controllers\Api\TripRequestController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Protected routes
Route::middleware('auth:api')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::get('/user', [AuthController::class, 'user']);

    // Trip Requests
    Route::apiResource('trip-requests', TripRequestController::class);
    Route::patch('trip-requests/{tripRequest}/status', [TripRequestController::class, 'updateStatus']);

    // Travelers (Users)
    Route::apiResource('travelers', TravelerController::class);
    Route::patch('travelers/{traveler}/restore', [TravelerController::class, 'restore']);

    // Destinations
    Route::get('destinations/countries', [DestinationController::class, 'countries']);
    Route::get('destinations/states', [DestinationController::class, 'states']);
    Route::apiResource('destinations', DestinationController::class);
});
