<?php

namespace App\Providers;

use App\Events\UserRegistered;
use App\Exceptions\Handler;
use App\Listeners\SendEmailVerificationNotification;
use App\Models\Account;
use App\Models\Budget;
use App\Models\BudgetTemplate;
use App\Models\Category;
use App\Models\CategoryPointer;
use App\Models\CategoryPointerTag;
use App\Models\Loan;
use App\Models\Tag;
use App\Models\Transaction;
use App\Observers\AccountObserver;
use App\Observers\BudgetObserver;
use App\Observers\BudgetTemplateObserver;
use App\Observers\CategoryObserver;
use App\Observers\CategoryPointerObserver;
use App\Observers\CategoryPointerTagObserver;
use App\Observers\LoanObserver;
use App\Observers\TagObserver;
use App\Observers\TransactionObserver;
use App\Services\ImportTransactions\DelimiterDetector;
use App\Services\ImportTransactions\NotificationService;
use App\Services\OwnerService;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(OwnerService::class);
        $this->app->singleton(NotificationService::class);
        $this->app->singleton(DelimiterDetector::class);
        $this->app->bind(ExceptionHandler::class, Handler::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::preventLazyLoading(!app()->isProduction());

        $this->registerObservers();
        $this->registerEventListeners();
    }

    private function registerObservers(): void
    {
        Account::observe(AccountObserver::class);
        Category::observe(CategoryObserver::class);
        Transaction::observe(TransactionObserver::class);
        CategoryPointer::observe(CategoryPointerObserver::class);
        CategoryPointerTag::observe(CategoryPointerTagObserver::class);
        Tag::observe(TagObserver::class);
        Loan::observe(LoanObserver::class);
        BudgetTemplate::observe(BudgetTemplateObserver::class);
        Budget::observe(BudgetObserver::class);
    }

    private function registerEventListeners(): void
    {
        Event::listen(
            UserRegistered::class,
            SendEmailVerificationNotification::class,
        );
    }
}
