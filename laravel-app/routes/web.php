<?php

use App\Http\Controllers\Auth\AuthenticationController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::view('/health', 'health')->name('health');

Route::middleware('guest:empresa')->group(function () {
    Route::get('/', [AuthenticationController::class, 'showLoginForm'])->name('login');
    Route::get('/login', [AuthenticationController::class, 'showLoginForm']);
    Route::post('/login', [AuthenticationController::class, 'login'])->name('login.submit');

    Route::get('/register', [AuthenticationController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthenticationController::class, 'register'])->name('register.submit');
});

Route::post('/logout', [AuthenticationController::class, 'logout'])->middleware('auth:empresa')->name('logout');

Route::middleware('auth:empresa')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::view('/employees/create', 'placeholders.employees-create')->name('employees.create');
    Route::view('/employees', 'placeholders.employees-index')->name('employees.index');
    Route::view('/attendance/capture', 'placeholders.attendance-capture')->name('attendance.capture');
    Route::view('/attendance', 'placeholders.attendance-index')->name('attendance.index');
});
