<?php

namespace App\Observers;

use App\Models\CategoryPointerTag;
use App\Services\OwnerService;

class CategoryPointerTagObserver
{
    public function created(CategoryPointerTag $categoryPointerTag): void
    {
        $user = OwnerService::make()->getUser();
        $user->categoryPointerTags()->syncWithoutDetaching($categoryPointerTag);
    }
}
