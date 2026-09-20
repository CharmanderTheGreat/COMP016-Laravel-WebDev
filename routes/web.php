<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/auth');
});

Route::middleware('guest')->group(function () {
    Route::get('/auth', [AuthController::class, 'show'])->name('auth.show');
    Route::post('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return 'Welcome, ' . auth()->user()->name . '! (placeholder dashboard)';
    })->name('dashboard');

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});