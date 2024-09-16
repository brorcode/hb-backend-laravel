<?php

namespace App\Observers;

use App\Models\CategoryPointer;
use App\Services\OwnerService;

class CategoryPointerObserver
{
    public function created(CategoryPointer $categoryPointer): void
    {
        $user = OwnerService::make()->getUser();
        $user->categoryPointers()->syncWithoutDetaching($categoryPointer);
    }
}
