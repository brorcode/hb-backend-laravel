<?php

namespace App\Jobs;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Services\ImportTransactions\ImportService;
use App\Services\OwnerService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Queue\Queueable;
use ReflectionClass;

class UpdateTransactionCategoriesJob implements ShouldQueue
{
    use Queueable;

    private User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
        // $this->onQueue('long-running');
    }

    public function tags(): array
    {
        return [(new ReflectionClass(static::class))->getShortName()];
    }

    public function handle(): void
    {
        $service = OwnerService::make();
        $service->setUser($this->user);

        $chunkNo = config('homebudget.chunk');
        Transaction::query()
            ->with([
                'category',
                'category.parentCategory',
            ])
            ->chunkById($chunkNo, function (Collection $transactions) {
                $transactions->each(function (Transaction $transaction) {
                    $this->checkTransaction($transaction);
                });
            })
        ;

        $this->removeEmptyCategories();
    }

    private function checkTransaction(Transaction $transaction): void
    {
        $service = ImportService::create();

        $childCategoryName = $transaction->category->name;
        $parentCategoryName = $transaction->category->parentCategory->name;

        $category = $service->getCategory($parentCategoryName, $childCategoryName);

        if ($transaction->category_id !== $category->getKey()) {
            $transaction->category_id = $category->getKey();
        }
        $isTransfer = $service->isBetweenAccounts($parentCategoryName);
        if ($transaction->is_transfer !== $isTransfer) {
            $transaction->is_transfer = $isTransfer;
        }

        if ($transaction->getDirty()) {
            $transaction->save();
        }
    }

    private function removeEmptyCategories(): void
    {
        Category::query()
            ->whereNotNull('parent_id')
            ->where('is_manual_created', false)
            ->whereDoesntHave('transactions')
            ->delete()
        ;
    }
}
