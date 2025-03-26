<?php

use App\Http\Controllers\Api\v1\AccountController;
use App\Http\Controllers\Api\v1\BudgetController;
use App\Http\Controllers\Api\v1\BudgetTemplateController;
use App\Http\Controllers\Api\v1\BudgetItemController;
use App\Http\Controllers\Api\v1\CategoryController;
use App\Http\Controllers\Api\v1\CategoryPointerController;
use App\Http\Controllers\Api\v1\DashboardController;
use App\Http\Controllers\Api\v1\DictionaryController;
use App\Http\Controllers\Api\v1\BudgetAnalyticsController;
use App\Http\Controllers\Api\v1\LoanController;
use App\Http\Controllers\Api\v1\NotificationController;
use App\Http\Controllers\Api\v1\TagController;
use App\Http\Controllers\Api\v1\TransactionController;
use App\Http\Controllers\Api\v1\UserController;
use App\Http\Controllers\Api\v1\UserProfileController;
use App\Models\Permission;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1', 'as' => 'api.v1.'], function () {
    Route::group(['middleware' => 'auth:sanctum'], function () {

        Route::group(['middleware' => 'emailVerified'], function () {
            Route::group(['prefix' => 'users', 'as' => 'users.'], function () {
                Route::group(['middleware' => 'permission:'.Permission::NAME_USERS_VIEW], function () {
                    Route::post('/', [UserController::class, 'index'])->name('index');
                    Route::get('/{user}', [UserController::class, 'show'])->name('show');
                });

                Route::group(['middleware' => 'permission:'.Permission::NAME_USERS_EDIT], function () {
                    Route::post('/store', [UserController::class, 'store'])->name('store');
                    Route::put('/{user}', [UserController::class, 'update'])->name('update');
                    Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
                });
            });

            Route::group(['prefix' => 'categories', 'as' => 'categories.'], function () {
                Route::group(['middleware' => 'permission:'.Permission::NAME_CATEGORIES_VIEW], function () {
                    Route::post('/parent', [CategoryController::class, 'parent'])->name('parent');
                    Route::post('/child', [CategoryController::class, 'child'])->name('child');
                    Route::get('/{category}', [CategoryController::class, 'show'])->name('show');
                });

                Route::group(['middleware' => 'permission:'.Permission::NAME_CATEGORIES_EDIT], function () {
                    Route::post('/store', [CategoryController::class, 'store'])->name('store');
                    Route::put('/{category}', [CategoryController::class, 'update'])->name('update');
                    Route::delete('/{category}', [CategoryController::class, 'destroy'])->name('destroy');
                });
            });

            Route::group(['prefix' => 'accounts', 'as' => 'accounts.'], function () {
                Route::group(['middleware' => 'permission:'.Permission::NAME_ACCOUNTS_VIEW], function () {
                    Route::post('/', [AccountController::class, 'index'])->name('index');
                    Route::get('/{account}', [AccountController::class, 'show'])->name('show');
                });

                Route::group(['middleware' => 'permission:'.Permission::NAME_ACCOUNTS_EDIT], function () {
                    Route::post('/store', [AccountController::class, 'store'])->name('store');
                    Route::put('/{account}', [AccountController::class, 'update'])->name('update');
                    Route::delete('/{account}', [AccountController::class, 'destroy'])->name('destroy');
                    Route::post('/{account_id}/transactions/import', [AccountController::class, 'import'])->name('import');
                });
            });

            Route::group(['prefix' => 'transactions', 'as' => 'transactions.'], function () {
                Route::group(['middleware' => 'permission:'.Permission::NAME_TRANSACTIONS_VIEW], function () {
                    Route::post('/', [TransactionController::class, 'index'])->name('index');
                    Route::get('/{transaction}', [TransactionController::class, 'show'])->name('show');
                });

                Route::group(['middleware' => 'permission:'.Permission::NAME_TRANSACTIONS_EDIT], function () {
                    Route::post('/store', [TransactionController::class, 'store'])->name('store');
                    Route::put('/{transaction}', [TransactionController::class, 'update'])->name('update');
                    Route::delete('/destroy-many', [TransactionController::class, 'destroyMany'])->name('destroy-many');
                    Route::delete('/{transaction}', [TransactionController::class, 'destroy'])->name('destroy');
                });
            });

            Route::group(['prefix' => 'tags', 'as' => 'tags.'], function () {
                Route::group(['middleware' => 'permission:'.Permission::NAME_TAGS_VIEW], function () {
                    Route::post('/', [TagController::class, 'index'])->name('index');
                    Route::get('/{tag}', [TagController::class, 'show'])->name('show');
                });

                Route::group(['middleware' => 'permission:'.Permission::NAME_TAGS_EDIT], function () {
                    Route::post('/store', [TagController::class, 'store'])->name('store');
                    Route::put('/{tag}', [TagController::class, 'update'])->name('update');
                    Route::delete('/{tag}', [TagController::class, 'destroy'])->name('destroy');
                    Route::post('/{tag_id}/transactions/attach', [TagController::class, 'attach'])->name('attach');
                    Route::post('/{tag_id}/transactions/detach', [TagController::class, 'detach'])->name('detach');
                });
            });

            Route::group(['prefix' => 'dictionary', 'as' => 'dictionary.'], function () {
                Route::post('/categories/parent', [DictionaryController::class, 'categoriesParent'])->name('categories.parent')
                    ->middleware('permission:'.Permission::NAME_CATEGORIES_VIEW)
                ;
                Route::post('/categories/child', [DictionaryController::class, 'categoriesChild'])->name('categories.child')
                    ->middleware('permission:'.Permission::NAME_CATEGORIES_VIEW)
                ;
                Route::post('/accounts', [DictionaryController::class, 'accounts'])->name('accounts')
                    ->middleware('permission:'.Permission::NAME_ACCOUNTS_VIEW)
                ;
                Route::post('/accounts-for-import', [DictionaryController::class, 'accountsForImport'])->name('accounts-for-import')
                    ->middleware('permission:'.Permission::NAME_ACCOUNTS_VIEW)
                ;
                Route::post('/tags', [DictionaryController::class, 'tags'])->name('tags')
                    ->middleware('permission:'.Permission::NAME_TAGS_VIEW)
                ;
                Route::post('/transactions/types', [DictionaryController::class, 'transactionTypes'])->name('transactions.types')
                    ->middleware('permission:'.Permission::NAME_TRANSACTIONS_VIEW)
                ;
                Route::post('/loans', [DictionaryController::class, 'loans'])->name('loans')
                    ->middleware('permission:'.Permission::NAME_LOANS_VIEW)
                ;
                Route::post('/loans/types', [DictionaryController::class, 'loanTypes'])->name('loans.types')
                    ->middleware('permission:'.Permission::NAME_LOANS_VIEW)
                ;
            });

            Route::group(['prefix' => 'category-pointers', 'as' => 'category-pointers.'], function () {
                Route::get('/', [CategoryPointerController::class, 'index'])->name('index')
                    ->middleware('permission:'.Permission::NAME_CATEGORY_POINTERS_VIEW)
                ;
                Route::post('/save', [CategoryPointerController::class, 'save'])->name('save')
                    ->middleware('permission:'.Permission::NAME_CATEGORY_POINTERS_EDIT)
                ;
            });

            Route::group(['prefix' => 'loans', 'as' => 'loans.'], function () {
                Route::group(['middleware' => 'permission:'.Permission::NAME_LOANS_VIEW], function () {
                    Route::post('/', [LoanController::class, 'index'])->name('index');
                    Route::get('/{loan}', [LoanController::class, 'show'])->name('show');
                });

                Route::group(['middleware' => 'permission:'.Permission::NAME_LOANS_EDIT], function () {
                    Route::post('/store', [LoanController::class, 'store'])->name('store');
                    Route::put('/{loan}', [LoanController::class, 'update'])->name('update');
                    Route::delete('/{loan}', [LoanController::class, 'destroy'])->name('destroy');
                });
            });
        });

        Route::group(['prefix' => 'dashboard', 'as' => 'dashboard.'], function () {
            Route::get('/balance', [DashboardController::class, 'balance'])->name('balance');
            Route::post('/debit-by-months', [DashboardController::class, 'debitByMonths'])->name('debit-by-months');
            Route::post('/credit-by-months', [DashboardController::class, 'creditByMonths'])->name('credit-by-months');
            Route::post('/total-by-months', [DashboardController::class, 'totalByMonths'])->name('total-by-months');

            Route::post('/debit-by-categories', [DashboardController::class, 'debitByCategories'])->name('debit-by-categories');
            Route::post('/credit-by-categories', [DashboardController::class, 'creditByCategories'])->name('credit-by-categories');
        });

        Route::group(['prefix' => 'profile', 'as' => 'user.profile.'], function () {
            Route::get('/', [UserProfileController::class, 'index'])->name('index');
            Route::put('/', [UserProfileController::class, 'update'])->name('update');
            Route::post('/email/verification', [UserProfileController::class, 'emailVerification'])
                ->middleware(['throttle:6,1'])
                ->name('email.verification')
            ;
        });

        Route::group(['prefix' => 'notifications', 'as' => 'notifications.'], function () {
            Route::get('/', [NotificationController::class, 'index'])->name('index');
            Route::post('/read', [NotificationController::class, 'read'])->name('read');
        });

        Route::group(['prefix' => 'budget-templates', 'as' => 'budget-templates.'], function () {
            Route::group(['middleware' => 'permission:'.Permission::NAME_BUDGETS_VIEW], function () {
                Route::post('/', [BudgetTemplateController::class, 'index'])->name('index');
                Route::get('/{budgetTemplate}', [BudgetTemplateController::class, 'show'])->name('show');
            });

            Route::group(['middleware' => 'permission:'.Permission::NAME_BUDGETS_EDIT], function () {
                Route::post('/store', [BudgetTemplateController::class, 'store'])->name('store');
                Route::put('/{budgetTemplate}', [BudgetTemplateController::class, 'update'])->name('update');
                Route::delete('/{budgetTemplate}', [BudgetTemplateController::class, 'destroy'])->name('destroy');
            });
        });

        Route::group(['prefix' => 'budgets', 'as' => 'budgets.'], function () {
            Route::group(['middleware' => 'permission:'.Permission::NAME_BUDGETS_VIEW], function () {
                Route::post('/', [BudgetController::class, 'index'])->name('index');
                Route::get('/{date}', [BudgetController::class, 'show'])->name('show');
            });

            Route::group(['middleware' => 'permission:'.Permission::NAME_BUDGETS_EDIT], function () {
                Route::post('/store', [BudgetController::class, 'store'])->name('store');
                Route::delete('/{date}', [BudgetController::class, 'destroy'])->name('destroy');
            });
        });

        Route::group(['prefix' => 'budget-items', 'as' => 'budget-items.'], function () {
            Route::group(['middleware' => 'permission:'.Permission::NAME_BUDGETS_VIEW], function () {
                Route::post('/', [BudgetItemController::class, 'index'])->name('index');
                Route::get('/{budgetItem}', [BudgetItemController::class, 'show'])->name('show');
            });

            Route::group(['middleware' => 'permission:'.Permission::NAME_BUDGETS_EDIT], function () {
                Route::post('/store', [BudgetItemController::class, 'store'])->name('store');
                Route::put('/{budgetItem}', [BudgetItemController::class, 'update'])->name('update');
                Route::delete('/{budgetItem}', [BudgetItemController::class, 'destroy'])->name('destroy');
            });
        });

        Route::group(['prefix' => 'budget-analytics', 'as' => 'budget-analytics.', 'middleware' => 'permission:'.Permission::NAME_BUDGETS_VIEW], function () {
            Route::post('/monthly', [BudgetAnalyticsController::class, 'monthly'])->name('monthly');
            Route::post('/monthly/categories/child', [BudgetAnalyticsController::class, 'childCategories'])->name('child-categories');
        });
    });
});
