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

    Route::get('/dashboard', function () {
        return view('pages.dashboard');
    })->name('dashboard');

    Route::prefix('chat')->name('chat.')->group(function () {
        Route::get('/', function () {
            return view('chat.index');
        })->name('index');
    });

    // User Management Routes
    Route::resource('users', UserController::class);
    Route::post('users/{user}/assign-permission', [UserController::class, 'assignPermission'])->name('users.assign-permission');
    Route::delete('users/{user}/permissions/{permission}', [UserController::class, 'removePermission'])->name('users.remove-permission');

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
