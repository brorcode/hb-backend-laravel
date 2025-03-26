<?php

namespace App\Http\Requests\Api\v1;

use App\Models\Budget;
use App\Services\Budget\BudgetService;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

/**
 * @property-read array $period_on
 */
class BudgetAnalyticsRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'period_on' => ['required', 'array'],
            'period_on.month' => ['required', 'integer', 'between:0,11'],
            'period_on.year' => [
                'required',
                'integer',
                'digits:4',
                'between:' . now()->subYears(50)->year . ',' . now()->addYears(50)->year,
            ],
        ];
    }
}
