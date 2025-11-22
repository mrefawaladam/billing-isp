<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
});

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
    });

    // Field Officer API Routes
    Route::prefix('field-officer')->middleware('staff.api')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Api\FieldOfficerApiController::class, 'dashboard']);
        Route::get('/customers', [\App\Http\Controllers\Api\FieldOfficerApiController::class, 'customers']);
        Route::get('/customers/{customer}', [\App\Http\Controllers\Api\FieldOfficerApiController::class, 'showCustomer']);
        Route::get('/map/customers', [\App\Http\Controllers\Api\FieldOfficerApiController::class, 'getCustomersForMap']);
        Route::get('/invoices/{invoice}', [\App\Http\Controllers\Api\FieldOfficerApiController::class, 'showInvoice']);
        Route::post('/invoices/{invoice}/pay', [\App\Http\Controllers\Api\FieldOfficerApiController::class, 'processPayment']);
    });
});

