<?php

namespace App\Observers;

use App\Exceptions\SystemException;
use App\Models\BudgetTemplate;
use App\Services\OwnerService;

class BudgetTemplateObserver
{
    /**
     * @throws SystemException
     */
    public function creating(BudgetTemplate $budgetTemplate): void
    {
        $user = OwnerService::make()->getUser();
        $budgetTemplate->user_id = $user->getKey();
    }
}
