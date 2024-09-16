<?php

namespace App\Observers;

use App\Models\Tag;
use App\Services\OwnerService;

class TagObserver
{
    public function created(Tag $tag): void
    {
        $user = OwnerService::make()->getUser();
        $user->tags()->syncWithoutDetaching($tag);
    }
}
