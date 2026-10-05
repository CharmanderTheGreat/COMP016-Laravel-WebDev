<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\Auth\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SessionsController;
use Illuminate\Support\Facades\Route;

Route::get("/attendance", [AttendanceController::class, "index"]);

Route::get('/register', [UserController::class, "create"]);
Route::post('/register', [UserController::class, "store"]);

Route::get('/login', [SessionsController::class, "create"]);
Route::post('/login', [SessionsController::class, "store"]);
