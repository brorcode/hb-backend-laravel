<?php

use App\Http\Controllers\Api\v1\UserController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1', 'as' => 'api.'], function () {
    Route::group(['middleware' => 'auth:sanctum'], function () {
        Route::group(['prefix' => 'users', 'as' => 'users.'], function () {
            Route::post('/', [UserController::class, 'index'])->name('index');
        });
    });
});
