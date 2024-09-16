<?php

namespace App\Http\Resources\Api\v1\Transaction;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionCollectionResource extends TransactionResource
{
    public function toArray(Request $request): array
    {
        $transaction = $this->getResource();
        $response = parent::toArray($request);
        $response['amount'] = $transaction->amount;

        return $response;
    }
}
