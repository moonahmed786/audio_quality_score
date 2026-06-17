<?php

use App\Http\Controllers\AudioUploadController;
use Illuminate\Support\Facades\Route;

Route::post('/upload', AudioUploadController::class);
