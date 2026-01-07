<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Authentication\AuthController;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\RoleAndPermission\RoleAndPermissionController;

/*
|--------------------------------------------------------------------------
| Auth Routes
|--------------------------------------------------------------------------
*/

Route::controller(AuthController::class)->prefix('auth')->group(function () {
    
    // --> Public Routes (Tidak butuh token)
    Route::post('register', 'register')->name('auth.register');
    Route::post('login', 'login')->name('auth.login');

    // --> Protected Routes (Butuh Middleware JWT)
    Route::middleware('jwt.custom')->group(function () {
        Route::post('logout', 'logout')->name('auth.logout');
        Route::post('refresh', 'refresh')->name('auth.refresh');
        Route::post('me', 'me')->name('auth.me');
    });

});

/*
|--------------------------------------------------------------------------
| User Routes
|--------------------------------------------------------------------------
*/
Route::controller(UserController::class)->prefix('users')->middleware('jwt.custom')->group(function () {
    Route::get('', 'index')->name('users.index')->middleware('permission:user.get');
    Route::post('', 'store')->name('users.store')->middleware('permission:user.create');
    Route::get('{id}', 'show')->name('users.show')->middleware('permission:user.show');
    Route::put('{id}', 'update')->name('users.update')->middleware('permission:user.update');
    Route::delete('{id}', 'destroy')->name('users.destroy')->middleware('permission:user.destroy');
});

/*
|--------------------------------------------------------------------------
| Role and Permission Routes
|--------------------------------------------------------------------------
*/
Route::controller(RoleAndPermissionController::class)
    ->prefix('roles-and-permissions')
    ->middleware(['jwt.custom', 'role:super-admin']) // Only super-admin can manage roles/permissions
    ->group(function () {
        Route::get('', 'index')->name('roles-permissions.index');
        Route::put('', 'update')->name('roles-permissions.update');
    });