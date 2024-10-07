<?php

namespace App\Services\Loan;

use App\Models\Loan;
use App\Services\ServiceInstance;

class LoanService
{
    use ServiceInstance;

    public function getAmountLeft(Loan $loan): int
    {
        $sum = match ($loan->type_id) {
            Loan::TYPE_ID_CREDIT => $loan->transactions->where('is_debit', true)->sum('amount'),
            Loan::TYPE_ID_DEBIT => $loan->transactions->where('is_debit', false)->sum('amount'),
            default => 0,
        };

        return $loan->amount - abs($sum);
    }
}
