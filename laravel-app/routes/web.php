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
    $resolveFaceApiUrl = static function (): string {
        $configured = config('services.face_api.base_url');
        if (!empty($configured)) {
            return $configured;
        }
        return rtrim((string) env('FACE_API_BASE_URL', ''), '/');
    };

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/employees/create', function () use ($resolveFaceApiUrl) {
        $empresa = auth('empresa')->user();
        return view('employees.create', [
            'empresa' => $empresa,
            'companyId' => $empresa?->cnpj,
            'faceApiBaseUrl' => $resolveFaceApiUrl(),
        ]);
    })->name('employees.create');

    Route::get('/employees', function () use ($resolveFaceApiUrl) {
        $empresa = auth('empresa')->user();
        return view('employees.index', [
            'empresa' => $empresa,
            'companyId' => $empresa?->cnpj,
            'faceApiBaseUrl' => $resolveFaceApiUrl(),
        ]);
    })->name('employees.index');

    Route::get('/attendance/capture', function () use ($resolveFaceApiUrl) {
        $empresa = auth('empresa')->user();
        return view('attendance.capture', [
            'empresa' => $empresa,
            'companyId' => $empresa?->cnpj,
            'faceApiBaseUrl' => $resolveFaceApiUrl(),
        ]);
    })->name('attendance.capture');

    Route::get('/attendance', function () use ($resolveFaceApiUrl) {
        $empresa = auth('empresa')->user();
        return view('attendance.index', [
            'empresa' => $empresa,
            'companyId' => $empresa?->cnpj,
            'faceApiBaseUrl' => $resolveFaceApiUrl(),
        ]);
    })->name('attendance.index');
});
