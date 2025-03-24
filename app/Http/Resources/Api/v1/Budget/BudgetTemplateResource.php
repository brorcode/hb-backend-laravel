<?php

namespace App\Http\Resources\Api\v1\Budget;

use App\Models\BudgetTemplate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BudgetTemplateResource extends JsonResource
{
    private function getResource(): BudgetTemplate
    {
        return $this->resource;
    }

    public function toArray(Request $request): array
    {
        $budgetTemplate = $this->getResource();

        return [
            'id' => $budgetTemplate->getKey(),
            'amount' => $budgetTemplate->amount / 100,
            'category' => $budgetTemplate->category->only(['id', 'name']),
        ];
    }
}
