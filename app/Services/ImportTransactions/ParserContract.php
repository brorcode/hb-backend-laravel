<?php

namespace App\Services\ImportTransactions;

use App\Models\Account;
use Carbon\Carbon;
use Illuminate\Support\Collection;

interface ParserContract
{
    public function parse(array $row, Account $account, ?Carbon $latestImportedDate): ?Collection;
}
