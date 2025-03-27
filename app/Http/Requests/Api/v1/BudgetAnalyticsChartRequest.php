<?php

namespace App\Http\Requests\Api\v1;

use App\Models\Budget;
use App\Models\Category;
use App\Rules\ExistForUserRule;
use App\Services\Budget\BudgetService;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

/**
 * @property-read int $category_id
 * @property-read bool $is_child
 */
class BudgetAnalyticsChartRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'category_id' => ['required', new ExistForUserRule(Category::class)],
            'is_child' => ['required', 'bool'],
        ];
    }
}
