<?php

namespace App\Observers;

use App\Exceptions\SystemException;
use App\Models\Account;
use App\Services\OwnerService;

class AccountObserver
{
    /**
     * @throws SystemException
     */
    public function created(Account $account): void
    {
        $user = OwnerService::make()->getUser();
        $user->accounts()->syncWithoutDetaching($account);
    }
}
