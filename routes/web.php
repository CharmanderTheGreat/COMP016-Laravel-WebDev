<?php

use App\Http\Controllers\Auth\UserController;
use App\Http\Controllers\Instructor\AttendanceController;
use App\Http\Controllers\Instructor\ProfileController as InstructorProfileController;
use App\Http\Controllers\Instructor\StudentController as InstructorStudentController;
use App\Http\Controllers\PhotoController;
use App\Http\Controllers\SessionsController;
use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Route;

// Home -> login (guests) or own home page (via the guest middleware redirect)
Route::redirect('/', '/login');

// ---- Guests only: sign up (students) + login (everyone) ----
Route::middleware('guest')->group(function () {
    Route::get('/register', [UserController::class, 'create']);
    Route::post('/register', [UserController::class, 'store']);

    Route::get('/login', [SessionsController::class, 'create'])->name('login');
    Route::post('/login', [SessionsController::class, 'store']);
});

Route::delete('/logout', [SessionsController::class, 'destroy'])->middleware('auth');

// ---- Profile pictures (served from the database; students see their own, the instructor sees all) ----
Route::get('/photos/{student}', [PhotoController::class, 'show'])
    ->middleware('auth')
    ->name('photos.show');

// ---- Student side ----
Route::middleware(['auth', 'role:student'])
    ->prefix('students')
    ->name('students.')
    ->group(function () {
        Route::get('/dashboard', [StudentController::class, 'dashboard'])->name('dashboard');
        Route::get('/profile', [StudentController::class, 'profile'])->name('profile');
        Route::post('/profile/photo', [StudentController::class, 'updatePhoto'])->name('profile.photo');
        Route::get('/attendance', [StudentController::class, 'history'])->name('attendance');
        Route::get('/attendance/export', [StudentController::class, 'export'])->name('attendance.export');
    });

// ---- Instructor side ----
Route::middleware(['auth', 'role:instructor'])
    ->prefix('instructor')
    ->name('instructor.')
    ->group(function () {
        Route::get('/students', [InstructorStudentController::class, 'index'])->name('students.index');
        Route::post('/students/{student}/reset-password', [InstructorStudentController::class, 'resetPassword'])
            ->name('students.reset-password');

        Route::get('/profile', [InstructorProfileController::class, 'show'])->name('profile');
        Route::put('/profile', [InstructorProfileController::class, 'update'])->name('profile.update');
        Route::put('/profile/password', [InstructorProfileController::class, 'password'])->name('profile.password');

        Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
        Route::get('/attendance/create', [AttendanceController::class, 'create'])->name('attendance.create');
        Route::get('/attendance/export', [AttendanceController::class, 'export'])->name('attendance.export');
        Route::post('/attendance', [AttendanceController::class, 'store'])->name('attendance.store');
        Route::get('/attendance/{date}', [AttendanceController::class, 'show'])
            ->where('date', '\d{4}-\d{2}-\d{2}')
            ->name('attendance.show');
    });
