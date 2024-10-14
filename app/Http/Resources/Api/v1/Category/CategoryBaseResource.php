<?php

namespace App\Http\Resources\Api\v1\Category;

use App\Models\Category;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryBaseResource extends JsonResource
{
    protected function formatTransactions($transactions): array
    {
        return [
            'count' => $transactions ? $transactions->count : 0,
            'amount' => $transactions ? $transactions->amount : 0,
        ];
    }

    protected function getTransactionData(
        Category $category,
        string $debitRelation,
        string $creditRelation,
        string $transferRelation,
    ): array
    {
        return [
            'transactions_debit' => $this->formatTransactions($category->$debitRelation->first()),
            'transactions_credit' => $this->formatTransactions($category->$creditRelation->first()),
            'transactions_transfer' => $this->formatTransactions($category->$transferRelation->first()),
        ];
    }
}
