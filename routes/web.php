<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Authentication Routes (Guest only)
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);

    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);

    Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');

    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');
});

// Logout (Authenticated only)
Route::middleware('auth')->post('/logout', [LoginController::class, 'logout'])->name('logout');

// Protected Routes
Route::middleware(['auth', 'session.timeout'])->group(function () {
    Route::get('/', function () {
        return redirect()->route('dashboard');
    });

    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/export', [\App\Http\Controllers\DashboardController::class, 'export'])->name('dashboard.export');

    Route::prefix('chat')->name('chat.')->group(function () {
        Route::get('/', function () {
            return view('chat.index');
        })->name('index');
    });

    // User Management Routes
    Route::resource('users', UserController::class);
    Route::post('users/{user}/assign-permission', [UserController::class, 'assignPermission'])->name('users.assign-permission');
    Route::delete('users/{user}/permissions/{permission}', [UserController::class, 'removePermission'])->name('users.remove-permission');

    // Customer Management Routes
    Route::resource('customers', \App\Http\Controllers\CustomerController::class);
    Route::get('customers/{customer}/devices', [\App\Http\Controllers\CustomerController::class, 'devices'])->name('customers.devices');
    Route::post('customers/bulk-assign', [\App\Http\Controllers\CustomerController::class, 'bulkAssign'])->name('customers.bulk-assign');

    // Device Management Routes (nested under customers)
    Route::get('customers/{customer}/devices/{device}', [\App\Http\Controllers\DeviceController::class, 'show'])->name('customers.devices.show');
    Route::post('customers/{customer}/devices', [\App\Http\Controllers\DeviceController::class, 'store'])->name('customers.devices.store');
    Route::put('customers/{customer}/devices/{device}', [\App\Http\Controllers\DeviceController::class, 'update'])->name('customers.devices.update');
    Route::delete('customers/{customer}/devices/{device}', [\App\Http\Controllers\DeviceController::class, 'destroy'])->name('customers.devices.destroy');

    // Invoice Management Routes
    Route::resource('invoices', \App\Http\Controllers\InvoiceController::class);
    Route::post('invoices/generate', [\App\Http\Controllers\InvoiceController::class, 'generate'])->name('invoices.generate');
    Route::get('invoices/{invoice}/print', [\App\Http\Controllers\InvoiceController::class, 'print'])->name('invoices.print');

    // Inventory Management Routes
    Route::resource('inventory', \App\Http\Controllers\InventoryController::class);
    Route::post('inventory/{inventory}/restock', [\App\Http\Controllers\InventoryController::class, 'restock'])->name('inventory.restock');
    Route::get('inventory/{inventory}/use', [\App\Http\Controllers\InventoryController::class, 'showUseForm'])->name('inventory.use.form');
    Route::post('inventory/{inventory}/use', [\App\Http\Controllers\InventoryController::class, 'useItem'])->name('inventory.use');
    Route::get('customers/{customer}/inventory-history', [\App\Http\Controllers\InventoryController::class, 'getCustomerUsageHistory'])->name('customers.inventory-history');

    // WhatsApp Notification Routes
    Route::prefix('whatsapp')->name('whatsapp.')->group(function () {
        Route::get('/', [\App\Http\Controllers\WhatsAppNotificationController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\WhatsAppNotificationController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\WhatsAppNotificationController::class, 'store'])->name('store');
        Route::get('/{whatsapp}', [\App\Http\Controllers\WhatsAppNotificationController::class, 'show'])->name('show');
        Route::post('/{whatsapp}/resend', [\App\Http\Controllers\WhatsAppNotificationController::class, 'resend'])->name('resend');
        Route::post('/invoices/{invoice}/send', [\App\Http\Controllers\WhatsAppNotificationController::class, 'sendInvoice'])->name('invoice.send');
    });

    // Payment Report Routes
    Route::get('payments/report', [\App\Http\Controllers\PaymentReportController::class, 'index'])->name('payments.report');
    Route::get('payments/report/export', [\App\Http\Controllers\PaymentReportController::class, 'exportCsv'])->name('payments.report.export');

    // Profile Routes
    Route::get('profile', [\App\Http\Controllers\ProfileController::class, 'index'])->name('profile.index');
    Route::put('profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');

    // Map Routes
    Route::get('map', [\App\Http\Controllers\MapController::class, 'index'])->name('map.index');
    Route::get('map/customers', [\App\Http\Controllers\MapController::class, 'getCustomers'])->name('map.customers');

    // Field Officer Routes
    Route::prefix('field-officer')->name('field-officer.')->middleware('staff')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\FieldOfficerController::class, 'dashboard'])->name('dashboard');
        Route::get('/customers', [\App\Http\Controllers\FieldOfficerController::class, 'customers'])->name('customers');
        Route::get('/customers/{customer}', [\App\Http\Controllers\FieldOfficerController::class, 'showCustomer'])->name('customers.show');
        Route::get('/map', [\App\Http\Controllers\FieldOfficerController::class, 'map'])->name('map');
        Route::get('/map/customers', [\App\Http\Controllers\FieldOfficerController::class, 'getCustomersForMap'])->name('map.customers');
        Route::get('/invoices/{invoice}/payment', [\App\Http\Controllers\FieldOfficerController::class, 'showPaymentForm'])->name('invoices.payment');
        Route::post('/invoices/{invoice}/payment', [\App\Http\Controllers\FieldOfficerController::class, 'processPayment'])->name('invoices.payment.process');
    });

    // Blank Pages
    Route::get('/blank', function () {
        return view('pages.blank-page');
    })->name('blank');

    Route::get('/blank-page', function () {
        return view('pages.blank');
    })->name('blank.page');

    Route::get('/blank-simple', function () {
        return view('pages.blank-simple');
    })->name('blank.simple');
});
