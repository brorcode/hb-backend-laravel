<?php

namespace App\Jobs;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Services\OwnerService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Collection;
use ReflectionClass;

class UpdateReturnMoneyTransactionsJob implements ShouldQueue
{
    use Queueable;

    private User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function tags(): array
    {
        return [(new ReflectionClass(static::class))->getShortName()];
    }

    public function handle(): void
    {
        OwnerService::make()->setUser($this->user);
        $chunk = config('homebudget.chunk');

        $categories = Category::query()->where('check_return', true)->get();
        $categories->each(function (Category $category) use ($chunk) {
            Transaction::query()
                ->where('category_id', $category->getKey())
                ->where('is_debit', false)
                ->where('is_transfer', false)
                ->chunkById($chunk, function (Collection $returnTransactions) use ($category) {
                    $returnTransactions->each(function (Transaction $returnTransaction) use ($category) {
                        $transaction = Transaction::query()
                            ->where('category_id', $category->getKey())
                            ->where('account_id', $returnTransaction->account_id)
                            ->where('is_debit', true)
                            ->where('is_transfer', false)
                            ->whereRaw("ABS(amount) >= {$returnTransaction->amount}")
                            ->orderByDesc('created_at')
                            ->first()
                        ;

                        if (!$transaction) {
                            return;
                        }

                        $transaction->amount = $transaction->amount + $returnTransaction->amount;
                        $transaction->save();

                        $returnTransaction->delete();
                    });
                })
            ;
        });
    }
}
