<?php

namespace App\Http\Resources\Api\v1\Budget;

use App\Models\Budget;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BudgetResource extends JsonResource
{
    private function getResource(): Budget
    {
        return $this->resource;
    }

    public function toArray(Request $request): array
    {
        $budget = $this->getResource();

        return [
            'id' => $budget->period_on->format('Ym'),
            'total' => $budget['total'] / 100,
            'period_on_for_list' => $budget->period_on->translatedFormat('Y F'),
            'period_on' => $budget->period_on->toDateString(),
        ];
    }
}
