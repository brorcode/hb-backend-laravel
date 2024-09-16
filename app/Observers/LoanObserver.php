<?php

namespace App\Observers;

use App\Exceptions\SystemException;
use App\Models\Loan;
use App\Services\OwnerService;

class LoanObserver
{
    /**
     * @throws SystemException
     */
    public function created(Loan $loan): void
    {
        $user = OwnerService::make()->getUser();
        $user->loans()->syncWithoutDetaching($loan);
    }
}
