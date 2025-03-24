<?php

namespace App\Http\Resources\Api\v1\Budget;

use App\Models\Budget;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BudgetItemResource extends JsonResource
{
    private function getResource(): Budget
    {
        return $this->resource;
    }

    public function toArray(Request $request): array
    {
        $budget = $this->getResource();
        $date = Carbon::parse($budget['period_on']);

        return [
            'id' => $budget->getKey(),
            'amount' => $budget->amount / 100,
            'category' => $budget->category->only(['id', 'name']),
            'period_on_for_list' => $date->translatedFormat('Y F'),
        ];
    }
}
