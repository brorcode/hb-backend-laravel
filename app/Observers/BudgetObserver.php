<?php

namespace App\Observers;

use App\Exceptions\SystemException;
use App\Models\Budget;
use App\Services\OwnerService;

class BudgetObserver
{
    /**
     * @throws SystemException
     */
    public function creating(Budget $budget): void
    {
        $user = OwnerService::make()->getUser();
        $budget->user_id = $user->getKey();
    }
}
