<?php

namespace App\Http\Resources\Api\v1;

use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AccountResource extends JsonResource
{
    private function getResource(): Account
    {
        return $this->resource;
    }

    public function toArray(Request $request): array
    {
        $account = $this->getResource();

        return [
            'id' => $account->getKey(),
            'name' => $account->name,
            'is_archived' => $account->is_archived,
            'amount' => $account->transactions->sum('amount') / 100,
            'created_at' => $account->created_at,
            'updated_at' => $account->updated_at,
        ];
    }
}
