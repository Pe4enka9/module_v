<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/authorization', [UserController::class, 'authorization']);
Route::post('/registration', [UserController::class, 'registration']);
Route::middleware(['auth:sanctum'])->get('/logout', [UserController::class, 'logout']);
Route::middleware(['auth:sanctum'])->post('/files', [UserController::class, 'files']);
