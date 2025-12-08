<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DeviceController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\RentController;
use App\Http\Controllers\Api\RentImageController;
use App\Http\Controllers\Api\RideController;
use App\Http\Controllers\Api\RoutineController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/programs', [AuthController::class, 'getPrograms']);
Route::get('/semesters', [AuthController::class, 'getSemesterOptions']);
Route::get('/departments', [AuthController::class, 'getDepartmentOptions']);
Route::get('/designations', [AuthController::class, 'getDesignationOptions']);

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);


// Protected routes
Route::middleware('auth:sanctum')->group(function () {

    // Rent routes
    Route::get('/rents', [RentController::class, 'index']);
    Route::get('/rents/{id}', [RentController::class, 'show']);
    Route::get('/rents/search', [RentController::class, 'search']);

    Route::post('/rents', [RentController::class, 'store']);
    Route::put('/rents/{id}', [RentController::class, 'update']);
    Route::delete('/rents/{id}', [RentController::class, 'destroy']);
    Route::put('/rents/{id}/availability', [RentController::class, 'setAvailability']);
    Route::get('/my-rents', [RentController::class, 'myRents']);

    // Ride routes
    Route::prefix('rides')->group(function () {
        Route::post('/create', [RideController::class, 'create']);
        Route::get('/nearby', [RideController::class, 'getNearbyRides']);
        Route::get('/my-rides', [RideController::class, 'myRides']);
        Route::get('/{id}', [RideController::class, 'show']);
        Route::post('/{id}/request', [RideController::class, 'requestRide']);
        Route::put('/{rideId}/request/{requestId}', [RideController::class, 'handleRequest']);
        Route::put('/{id}/status', [RideController::class, 'updateStatus']);
        Route::post('/{id}/location', [RideController::class, 'updateLocation']);
        Route::get('/{id}/locations', [RideController::class, 'getLocations']);
        Route::post('/{id}/message', [RideController::class, 'sendMessage']);
        Route::get('/{id}/messages', [RideController::class, 'getMessages']);
    });

    // Device routes for push notifications
    Route::prefix('device')->group(function () {
        Route::post('/register', [DeviceController::class, 'registerDevice']);
        Route::delete('/unregister/{token}', [DeviceController::class, 'unregisterDevice']);
    });

    // User routes
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/{user}', [UserController::class, 'show']);
    Route::get('/me', [UserController::class, 'me']);
    Route::put('/profile-update', [UserController::class, 'updateProfile']);
    Route::post('/logout', [AuthController::class, 'logout']);
});


Route::get('notices', [HomeController::class, 'notices']);
Route::post('/routine', [RoutineController::class, 'getRoutine']);

// 60 = number of requests allowed
// 1 = number of minutes
// Route::middleware('throttle:5,1')->group(function () {
// });
