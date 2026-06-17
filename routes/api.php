<?php

use App\Http\Controllers\AudioUploadController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/register', [AuthController::class, 'register'])->middleware('throttle:api-auth');
Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:api-auth');

Route::middleware('auth.api')->group(function (): void {
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/upload', AudioUploadController::class)->middleware('throttle:upload');
});
