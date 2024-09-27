<?php

namespace App\Observers;

use App\Exceptions\SystemException;
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
}
