<?php

namespace App\Http\Requests\Api\v1;

use App\Models\Budget;
use App\Models\Category;
use App\Rules\ExistForUserRule;
use App\Rules\UniqueRelationForUserRule;
use App\Services\Budget\BudgetService;

/**
 * @property-read Budget|null $budget
 *
 * @property-read string $amount
 * @property-read int $category_id
 * @property-read int $period_on
 */
class BudgetItemUpsertRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:0.01'],
            'category_id' => [
                'required',
                new ExistForUserRule(Category::class),
                new UniqueRelationForUserRule(Budget::class, $this->budget, [
                    'column' => 'period_on',
                    'value' => BudgetService::getPeriodOnFromInt($this->period_on ?? 0)->toDateString(),
                ]),
            ],
            'period_on' => ['required', 'integer'],
        ];
    }
}
