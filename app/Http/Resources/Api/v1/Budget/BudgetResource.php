<?php

namespace App\Http\Resources\Api\v1\Budget;

use App\Models\Budget;
use Carbon\Carbon;
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
        $periodOn = Carbon::parse($budget['period_on']);

        return [
            'id' => $periodOn->format('Ym'),
            'total' => $budget['total'] / 100,
            'period_on_for_list' => $periodOn->translatedFormat('Y F'),
            'period_on' => $budget['period_on'],
            'deletable' => $periodOn->gt(now()),
        ];
    }
}
