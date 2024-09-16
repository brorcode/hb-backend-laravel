<?php

namespace App\Observers;

use App\Exceptions\SystemException;
use App\Models\CategoryPointer;
use App\Services\OwnerService;

class CategoryPointerObserver
{
    /**
     * @throws SystemException
     */
    public function created(CategoryPointer $categoryPointer): void
    {
        $user = OwnerService::make()->getUser();
        $user->categoryPointers()->syncWithoutDetaching($categoryPointer);
    }
}
