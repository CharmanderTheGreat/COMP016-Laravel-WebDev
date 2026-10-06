<?php

use App\Http\Controllers\Auth\UserController;
use App\Http\Controllers\SessionsController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\Instructor;
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
Route::middleware(['auth', 'role:students'])
    ->prefix('students')
    ->name('students.')
    ->group(function () {
        Route::get('/dashboard', [StudentController::class, 'dashboard'])->name('dashboard');
        Route::get('/profile', [StudentController::class, 'profile'])->name('profile');
        Route::get('/attendance', [StudentController::class, 'history'])->name('attendance');
    });

// ---- Instructor side (placeholder; the instructor dev replaces this) ----
Route::middleware(['auth', 'role:instructor'])
    ->prefix('instructor')
    ->group(function () {
        Route::get("/students", [Instructor\StudentController::class, 'index'])->name('students');

        Route::get("/attendance", [Instructor\AttendanceController::class, 'index']);
        Route::get("/attendance/{date}", [Instructor\AttendanceController::class, 'show'])
        ->where('date', '\d{4}-\d{2}-\d{2}')
        ->name('instructor.attendance.show');

        Route::get("/attendance/create", [Instructor\AttendanceController::class, 'create']);
        Route::post("/attendance/create", [Instructor\AttendanceController::class, 'store']);
    });

