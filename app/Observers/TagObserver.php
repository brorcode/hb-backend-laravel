<?php

namespace App\Observers;

use App\Exceptions\SystemException;
use App\Models\Tag;
use App\Services\OwnerService;

class TagObserver
{
    /**
     * @throws SystemException
     */
    public function created(Tag $tag): void
    {
        $user = OwnerService::make()->getUser();
        $user->tags()->syncWithoutDetaching($tag);
    }
}
