<?php

namespace App\Console\Commands;

use App\Models\Scopes\OwnerScope;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;


class RemoveBeforeJuly2015Transactions extends Command
{
    protected $signature = 'app:remove-transactions';
    protected $description = 'One time command to remove old transactions before July 2015.';

    public function handle(): void
    {
        $count = Transaction::query()
            ->withoutGlobalScope(OwnerScope::class)
            ->where('created_at', '<=', Carbon::createFromDate(2015, 7, 1)
            ->toDateTimeString())
            ->delete()
        ;

        $this->info('Remove ' . $count . ' transactions');
    }
}
