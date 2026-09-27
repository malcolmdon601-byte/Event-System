<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\EquipmentController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\PortalController;
use App\Http\Controllers\Api\PublicController;
use App\Http\Controllers\Api\QuotationController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\StaffController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\VendorController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public routes — no authentication required
|--------------------------------------------------------------------------
*/
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/register', [AuthController::class, 'registerCustomer']);

Route::get('/public/services', [PublicController::class, 'services']);
Route::get('/public/event-types', [PublicController::class, 'eventTypes']);
Route::post('/public/request-quote', [PublicController::class, 'requestQuote']);

/*
|--------------------------------------------------------------------------
| Authenticated routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);

    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead']);

    Route::get('/events/{event}/messages', [MessageController::class, 'index']);
    Route::post('/events/{event}/messages', [MessageController::class, 'store']);

    // Customer portal — customer role only
    Route::middleware('role:customer')->group(function () {
        Route::get('/portal/dashboard', [PortalController::class, 'dashboard']);
    });

    Route::middleware('role:customer,super_admin,manager,finance')->group(function () {
        Route::post('/quotations/{quotation}/accept', [QuotationController::class, 'accept']);
        Route::post('/quotations/{quotation}/reject', [QuotationController::class, 'reject']);
    });

    // Staff / management area — everyone except plain customers
    Route::middleware('role:super_admin,manager,finance,staff')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index']);

        Route::apiResource('customers', CustomerController::class);
        Route::apiResource('events', EventController::class);
        Route::post('/events/{event}/services', [EventController::class, 'attachServices']);
        Route::post('/events/{event}/staff', [EventController::class, 'assignStaff']);
        Route::post('/events/{event}/vendors', [EventController::class, 'assignVendors']);

        Route::apiResource('services', ServiceController::class);
        Route::apiResource('staff', StaffController::class);
        Route::apiResource('vendors', VendorController::class);
        Route::apiResource('tasks', TaskController::class)->except(['show']);

        Route::get('/equipment', [EquipmentController::class, 'index']);
        Route::post('/equipment', [EquipmentController::class, 'store'])->middleware('role:super_admin,manager');
        Route::post('/equipment/{equipment}/reserve', [EquipmentController::class, 'reserve']);
        Route::delete('/equipment-reservations/{reservation}', [EquipmentController::class, 'destroyReservation']);
    });

    // Quotations / payments — finance-led but managers can also work them
    Route::middleware('role:super_admin,manager,finance')->group(function () {
        Route::apiResource('quotations', QuotationController::class)->only(['index', 'store', 'show']);
        Route::apiResource('payments', PaymentController::class)->only(['index', 'store', 'show']);
    });
});
