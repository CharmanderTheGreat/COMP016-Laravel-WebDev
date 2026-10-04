<?php

use App\Http\Controllers\Auth\UserController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;


Route::get('/register', [UserController::class, "create"]);
Route::post('/register', [UserController::class, "store"]);
