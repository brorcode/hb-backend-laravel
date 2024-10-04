<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'api/v1', 'as' => 'api.v1.'], function () {
    Route::post('/register', [RegisteredUserController::class, 'store'])
        ->middleware('api.guest')
        ->name('register');

    Route::post('/login', [AuthenticatedSessionController::class, 'store'])
        ->middleware('api.guest')
        ->name('login');

    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
        ->middleware('api.guest')
        ->name('password.email');

    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
        ->middleware('auth')
        ->name('logout');
});

Route::get('/verify-email/{id}/{hash}', VerifyEmailController::class)
    ->middleware(['auth', 'signed', 'throttle:6,1'])
    ->name('verification.verify');

Route::post('/reset-password', [NewPasswordController::class, 'store'])
    ->middleware('api.guest')
    ->name('password.reset');
