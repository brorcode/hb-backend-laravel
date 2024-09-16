<?php

namespace App\Observers;

use App\Exceptions\SystemException;
use App\Models\CategoryPointerTag;
use App\Services\OwnerService;

class CategoryPointerTagObserver
{
    /**
     * @throws SystemException
     */
    public function created(CategoryPointerTag $categoryPointerTag): void
    {
        $user = OwnerService::make()->getUser();
        $user->categoryPointerTags()->syncWithoutDetaching($categoryPointerTag);
    }
}
