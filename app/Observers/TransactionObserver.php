<?php

namespace App\Observers;

use App\Exceptions\SystemException;
use App\Models\Transaction;
use App\Services\OwnerService;
use Illuminate\Validation\ValidationException;

class TransactionObserver
{
    /**
     * @throws ValidationException
     */
    public function creating(Transaction $transaction): void
    {
        $this->checkTransaction($transaction);
        $this->checkTransfer($transaction);
    }

    /**
     * @throws SystemException
     */
    public function created(Transaction $transaction): void
    {
        $user = OwnerService::make()->getUser();
        $user->transactions()->syncWithoutDetaching($transaction);
    }

    /**
     * @throws ValidationException
     */
    public function updating(Transaction $transaction): void
    {
        $this->checkTransaction($transaction);
    }

    /**
     * @throws ValidationException
     */
    private function checkTransaction(Transaction $transaction): void
    {
        if (!$transaction->is_debit) {
            $transaction->amount = abs($transaction->amount) * -1;
        }

        if (!$transaction->category) {
            throw ValidationException::withMessages([
                'category_id' => ['Поле Категория обязательно для заполнения.'],
            ]);
        }

        if ($transaction->category->isParent()) {
            throw ValidationException::withMessages([
                'category_id' => ['Вы не можете создать транзакцию для родительской категории.'],
            ]);
        }
    }

    /**
     * @throws ValidationException
     */
    private function checkTransfer(Transaction $transaction): void
    {
        if (!$transaction->is_transfer) {
            return;
        }
        if (!$accountTo = request()->input('account_to')) {
            return;
        }
        if ($transaction->is_debit) {
            throw ValidationException::withMessages([
                'is_debit' => ['Данная транзакция должна быть расходом.'],
            ]);
        }
        if ($transaction->account_id === (int) $accountTo) {
            throw ValidationException::withMessages([
                'account_to' => ['Нельзя перевести на тот же счет.'],
            ]);
        }

        request()->request->remove('account_to');

        $createdAt = $transaction->created_at->addSecond();
        $newTransaction = new Transaction();
        $newTransaction->category_id = $transaction->category_id;
        $newTransaction->amount = abs($transaction->amount);
        $newTransaction->account_id = $accountTo;
        $newTransaction->is_debit = true;
        $newTransaction->is_transfer = true;
        $newTransaction->created_at = $createdAt;
        $newTransaction->updated_at = $createdAt;
        $newTransaction->save();
    }
}
