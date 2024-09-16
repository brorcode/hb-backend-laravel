<?php

namespace App\Observers;

use App\Exceptions\SystemException;
use App\Jobs\UpdateReturnMoneyTransactionsJob;
use App\Models\Category;
use App\Services\OwnerService;

class CategoryObserver
{
    /**
     * @throws SystemException
     */
    public function created(Category $category): void
    {
        $user = OwnerService::make()->getUser();
        $user->categories()->syncWithoutDetaching($category);
    }

    /**
     * @throws SystemException
     */
    public function updated(Category $category): void
    {
        if ($category->isDirty('check_return') && $category->check_return) {
            $user = OwnerService::make()->getUser();
            UpdateReturnMoneyTransactionsJob::dispatch($user);
        }
    }
}
