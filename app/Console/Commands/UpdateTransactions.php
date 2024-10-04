<?php

namespace App\Console\Commands;

use App\Models\Scopes\OwnerScope;
use App\Models\Transaction;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;


class UpdateTransactions extends Command
{
    protected $signature = 'app:update-transactions';
    protected $description = 'One time command to update transactions. To change debit and credit';

    public function handle(): void
    {
        $total = Transaction::query()->withoutGlobalScope(OwnerScope::class)->count();
        $updatedCount = 0;

        Transaction::query()
            ->withoutGlobalScope(OwnerScope::class)
            ->chunkById(config('homebudget.chunk'), function (Collection $transactions) use ($total, &$updatedCount) {
                $transactions->each(function (Transaction $transaction) use (&$updatedCount) {
                    $transaction->is_debit = !$transaction->is_debit;
                    $transaction->saveQuietly();
                    $updatedCount++;
                });
                $this->info('Updated ' . $updatedCount . ' of ' . $total . ' transactions');
        });
    }
}
