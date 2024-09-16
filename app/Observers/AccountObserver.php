<?php

namespace App\Observers;

use App\Models\Account;
use App\Services\OwnerService;

class AccountObserver
{
    public function created(Account $account): void
    {
        $user = OwnerService::make()->getUser();
        $user->accounts()->syncWithoutDetaching($account);
    }
}
