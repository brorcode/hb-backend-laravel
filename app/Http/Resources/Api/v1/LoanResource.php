<?php

namespace App\Http\Resources\Api\v1;

use App\Models\Loan;
use App\Services\Loan\LoanService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LoanResource extends JsonResource
{
    private function getResource(): Loan
    {
        return $this->resource;
    }

    public function toArray(Request $request): array
    {
        $loan = $this->getResource();
        $service = LoanService::create();

        return [
            'id' => $loan->getKey(),
            'name' => $loan->name,
            'type' => [
                'id' => $loan->type_id,
                'name' => Loan::TYPES[$loan->type_id],
            ],
            'amount' => $loan->amount / 100,
            'amount_left' => $service->getAmountLeft($loan),
            'deadline_on' => $loan->deadline_on,
            'created_at' => $loan->created_at,
            'updated_at' => $loan->updated_at,
        ];
    }
}
