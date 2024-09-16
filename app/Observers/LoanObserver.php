<?php

namespace App\Observers;

use App\Models\Loan;
use App\Services\OwnerService;

class LoanObserver
{
    public function created(Loan $loan): void
    {
        $user = OwnerService::make()->getUser();
        $user->loans()->syncWithoutDetaching($loan);
    }
}
