<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
// routes/web.php
// use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\ResetPasswordController;

Route::get('reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])
     ->name('password.reset');

Route::post('reset-password', [ResetPasswordController::class, 'reset'])
     ->name('password.update');

// ... other routes ...

Route::get('reset-password/{token}', [UserController::class, 'showResetForm'])->name('password.reset');

Route::post('reset-password', [UserController::class, 'reset'])->name('password.update');

Route::get('/', function () {
    return view('welcome');
});
