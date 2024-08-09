<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\UsersController;
use Illuminate\Support\Facades\Route;


Route::get('/login', [LoginController::class, 'showLoginForm'])->name('show_login');
Route::post('/login', [LoginController::class, 'handleLogin'])->name('login');
Route::group(['middleware'=>'auth:web'], function() {
    Route::post('/logout', [LoginController::class, 'handleLogout'])->name('logout');
});

Route::group(['middleware'=>'auth:web'], function() {
    Route::get('/', [HomeController::class, 'index']);
    Route::get('/charts', [HomeController::class, 'charts']);
    Route::resource('/users', UsersController::class);
    Route::post('/users/bulkDelete', [UsersController::class, 'bulkDelete']);
});
