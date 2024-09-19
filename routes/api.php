<?php

use App\Http\Controllers\Api\v1\AccountController;
use App\Http\Controllers\Api\v1\CategoryController;
use App\Http\Controllers\Api\v1\CategoryPointerController;
use App\Http\Controllers\Api\v1\DictionaryController;
use App\Http\Controllers\Api\v1\TagController;
use App\Http\Controllers\Api\v1\TransactionController;
use App\Http\Controllers\Api\v1\UserController;
use App\Http\Controllers\Api\v1\UserProfileController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1', 'as' => 'api.v1.'], function () {
    Route::group(['middleware' => 'auth:sanctum'], function () {
        Route::group(['prefix' => 'users', 'as' => 'users.'], function () {
            Route::post('/', [UserController::class, 'index'])->name('index');
            Route::post('/store', [UserController::class, 'store'])->name('store');
            Route::get('/{user}', [UserController::class, 'show'])->name('show');
            Route::put('/{user}', [UserController::class, 'update'])->name('update');
            Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
        });

        Route::group(['prefix' => 'profile', 'as' => 'user.profile.'], function () {
            Route::get('/', [UserProfileController::class, 'index'])->name('index');
            Route::put('/', [UserProfileController::class, 'update'])->name('update');
            Route::post('/email/verification', [UserProfileController::class, 'emailVerification'])
                ->middleware(['throttle:6,1'])
                ->name('email.verification')
            ;
        });

        Route::group(['prefix' => 'categories', 'as' => 'categories.'], function () {
            Route::post('/', [CategoryController::class, 'index'])->name('index');
            Route::post('/store', [CategoryController::class, 'store'])->name('store');
            Route::get('/{category}', [CategoryController::class, 'show'])->name('show');
            Route::put('/{category}', [CategoryController::class, 'update'])->name('update');
            Route::delete('/{category}', [CategoryController::class, 'destroy'])->name('destroy');
        });
        Route::group(['prefix' => 'accounts', 'as' => 'accounts.'], function () {
            Route::post('/', [AccountController::class, 'index'])->name('index');
            Route::post('/store', [AccountController::class, 'store'])->name('store');
            Route::get('/{account}', [AccountController::class, 'show'])->name('show');
            Route::put('/{account}', [AccountController::class, 'update'])->name('update');
            Route::delete('/{account}', [AccountController::class, 'destroy'])->name('destroy');
            Route::post('/{account_id}/transactions/import', [AccountController::class, 'import'])->name('import');
        });
        Route::group(['prefix' => 'transactions', 'as' => 'transactions.'], function () {
            Route::post('/', [TransactionController::class, 'index'])->name('index');
            Route::post('/store', [TransactionController::class, 'store'])->name('store');
            Route::get('/{transaction}', [TransactionController::class, 'show'])->name('show');
            Route::put('/{transaction}', [TransactionController::class, 'update'])->name('update');
            Route::delete('/destroy-many', [TransactionController::class, 'destroyMany'])->name('destroy-many');
            Route::delete('/{transaction}', [TransactionController::class, 'destroy'])->name('destroy');
        });
        Route::group(['prefix' => 'tags', 'as' => 'tags.'], function () {
            Route::post('/', [TagController::class, 'index'])->name('index');
            Route::post('/store', [TagController::class, 'store'])->name('store');
            Route::get('/{tag}', [TagController::class, 'show'])->name('show');
            Route::put('/{tag}', [TagController::class, 'update'])->name('update');
            Route::delete('/{tag}', [TagController::class, 'destroy'])->name('destroy');
            Route::post('/{tag_id}/transactions/attach', [TagController::class, 'attach'])->name('attach');
            Route::post('/{tag_id}/transactions/detach', [TagController::class, 'detach'])->name('detach');
        });
        Route::group(['prefix' => 'dictionary', 'as' => 'dictionary.'], function () {
            Route::post('/categories', [DictionaryController::class, 'categories'])->name('categories');
            Route::post('/accounts', [DictionaryController::class, 'accounts'])->name('accounts');
            Route::post('/tags', [DictionaryController::class, 'tags'])->name('tags');
        });
        Route::group(['prefix' => 'category-pointers', 'as' => 'category-pointers.'], function () {
            Route::get('/', [CategoryPointerController::class, 'index'])->name('index');
            Route::post('/save', [CategoryPointerController::class, 'save'])->name('save');
        });
    });
});
