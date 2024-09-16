<?php

namespace App\Http\Resources\Api\v1\Transaction;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    protected function getResource(): Transaction
    {
        return $this->resource;
    }

    public function toArray(Request $request): array
    {
        $transaction = $this->getResource();

        return [
            'id' => $transaction->getKey(),
            'category' => $transaction->category->only(['id', 'name']),
            'account' => $transaction->account->only(['id', 'name']),
            'tags' => $transaction->tags->pluck('name')->toArray(),
            'is_debit' => $transaction->is_debit,
            'is_transfer' => $transaction->is_transfer,
            'created_at' => $transaction->created_at,
            'updated_at' => $transaction->updated_at,
        ];
    }
}
