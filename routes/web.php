<?php

use App\Http\Controllers\Auth\UserController;
use App\Http\Controllers\SessionsController;
use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Route;

// Home -> login (guests) or own dashboard (via the guest middleware redirect)
Route::redirect('/', '/login');

// ---- Guests only: sign up (students) + login (everyone) ----
Route::middleware('guest')->group(function () {
    Route::get('/register', [UserController::class, 'create']);
    Route::post('/register', [UserController::class, 'store']);

    Route::get('/login', [SessionsController::class, 'create'])->name('login');
    Route::post('/login', [SessionsController::class, 'store']);
});

Route::post('/logout', [SessionsController::class, 'destroy'])->middleware('auth');

// ---- Student side ----
Route::middleware(['auth', 'role:student'])
    ->prefix('student')
    ->name('student.')
    ->group(function () {
        Route::get('/dashboard', [StudentController::class, 'dashboard'])->name('dashboard');
        Route::get('/profile', [StudentController::class, 'profile'])->name('profile');
        Route::get('/attendance', [StudentController::class, 'history'])->name('attendance');
    });

// ---- Instructor side (placeholder; the instructor dev replaces this) ----
Route::middleware(['auth', 'role:instructor'])
    ->get('/instructor/dashboard', fn () => view('instructor.dashboard'));