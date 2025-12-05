<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'
], function ($router) {
    Route::post('login', [\App\Http\Controllers\AuthController::class, 'login']);
    Route::post('register', [\App\Http\Controllers\AuthController::class, 'register']);
    Route::post('logout', [\App\Http\Controllers\AuthController::class, 'logout'])->middleware('jwt.custom');
    Route::post('refresh', [\App\Http\Controllers\AuthController::class, 'refresh'])->middleware('jwt.custom');
    Route::post('me', [\App\Http\Controllers\AuthController::class, 'me'])->middleware('jwt.custom');
});


