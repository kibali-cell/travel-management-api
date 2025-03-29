<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\PasswordResetController;
use App\Http\Controllers\API\HotelController;
use App\Http\Controllers\API\FlightController;
use App\Http\Controllers\API\UserProfileController;
use App\Http\Controllers\API\TripController;
use App\Http\Controllers\API\BookingController;
use App\Http\Controllers\API\ExpenseController;
use App\Http\Controllers\API\NotificationController;
use App\Http\Controllers\API\PolicyController;
use App\Http\Controllers\API\ApprovalController;
use App\Http\Controllers\API\CompanyController;
use App\Http\Controllers\API\DepartmentController;
use App\Http\Controllers\API\ApiTestController;

// Public Routes (No Authentication Required)
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/password/email', [PasswordResetController::class, 'sendResetLinkEmail'])->name('password.email');
Route::post('/password/reset', [PasswordResetController::class, 'reset'])->name('password.reset');
Route::get('/hotels/search', [HotelController::class, 'searchByCity']);
Route::get('/flights/search', [FlightController::class, 'search']);
Route::post('/flights/search', [FlightController::class, 'search']);
Route::post('/flights/resolve_iata', [FlightController::class, 'resolveIataCode']);
Route::get('/hotels/autocomplete', [HotelController::class, 'autocomplete']);

// Protected Routes (Requires Authentication via Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    // General Routes (Accessible to All Authenticated Users)
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Employee Routes (Accessible to employee, travel_admin, and super_admin)
    Route::middleware('role:employee,travel_admin,super_admin')->group(function () {
        // Profile Management
        Route::get('/profile', [UserProfileController::class, 'show']);
        Route::put('/profile', [UserProfileController::class, 'update']);
        Route::put('/profile/password', [UserProfileController::class, 'updatePassword']);
        Route::put('/profile/language', [UserProfileController::class, 'updateLanguage']);
        Route::put('/profile/notifications', [UserProfileController::class, 'updateNotificationPreferences']);

        // Trip, Booking, and Expense Management
        Route::apiResource('trips', TripController::class);
        Route::apiResource('bookings', BookingController::class);
        Route::apiResource('expenses', ExpenseController::class);

        // Booking Flights and Hotels
        Route::post('/flights/book', [FlightController::class, 'book']);
        Route::post('/hotels/book', [HotelController::class, 'book']);
        Route::get('/hotels/offers', [HotelController::class, 'getOffersByHotelIds']);

        // Notifications
        Route::post('/notifications/send', [NotificationController::class, 'sendEmail']);

        // Expose the user listing route (GET /users) so that employees can see users from their company.
        Route::get('/users', [UserController::class, 'index']);
    });

    // Travel Admin Routes (Accessible to travel_admin and super_admin)
    Route::middleware('role:travel_admin|super_admin')->group(function () {
        // Policy Management
        Route::apiResource('policies', PolicyController::class);

        // Approval Management
        Route::post('/approvals', [ApprovalController::class, 'store']);
        Route::apiResource('approvals', ApprovalController::class)->only(['update']);

        // Company Settings (Partial Access for Travel Admins)
        Route::put('companies/{company}/settings', [CompanyController::class, 'updateSettings']);

        // User Management (Create, Update, Delete, and Show user details)
        Route::post('/users', [UserController::class, 'store']);
        Route::put('/users/{user}', [UserController::class, 'update']);
        Route::delete('/users/{user}', [UserController::class, 'destroy']);
        Route::get('/users/{user}', [UserController::class, 'show']);
    });

    // Super Admin Routes (Accessible only to super_admin)
    Route::middleware('role:super_admin')->group(function () {
        // Company and Department Management
        Route::apiResource('companies', CompanyController::class);
        Route::apiResource('departments', DepartmentController::class);

        // Test Routes
        Route::get('/test/amadeus', [ApiTestController::class, 'testAmadeus']);
        Route::get('/test/travelduqa', [ApiTestController::class, 'testTravelDuqa']);
    });
});
