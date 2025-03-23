<?php

namespace App\Http\Requests\Api\v1;

use App\Models\BudgetTemplate;
use App\Models\Category;
use App\Rules\ExistForUserRule;
use App\Rules\UniqueRelationForUserRule;

/**
 * @property-read BudgetTemplate|null $budgetTemplate
 *
 * @property-read string $amount
 * @property-read int $category_id
 */
class BudgetTemplateUpsertRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:0.01'],
            'category_id' => [
                'required',
                new ExistForUserRule(Category::class),
                new UniqueRelationForUserRule(BudgetTemplate::class, $this->budgetTemplate),
            ],
        ];
    }
}
