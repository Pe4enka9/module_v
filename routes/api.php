<?php

use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/authorization', [UserController::class, 'authorization']);
Route::post('/registration', [UserController::class, 'registration']);
Route::get('/logout', [UserController::class, 'logout']);
