<?php

namespace App\Http\Requests\Api\v1;

use AllowDynamicProperties;
use App\Models\Budget;
use App\Services\Budget\BudgetService;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

/**
 * @property-read array $period_on
 */
class BudgetUpsertRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'period_on' => ['required', 'array'],
            'period_on.month' => ['required:period_on', 'integer', 'between:0,11'],
            'period_on.year' => [
                'required:period_on',
                'integer',
                'digits:4',
                'between:' . now()->subYear()->year . ',' . now()->addYear()->year,
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'period_on' => 'Дата',
            'period_on.month' => 'Дата',
            'period_on.year' => 'Дата',
        ];
    }

    /**
     * @throws ValidationException
     */
    public function rulesPassed(): void
    {
        $budget = Budget::query()
            ->where('period_on', BudgetService::getPeriodOnFromArray($this->period_on)->toDateString())
            ->exists()
        ;

        if ($budget) {
            throw ValidationException::withMessages([
                'period_on' => ['Бюждет на эту дату уже существует.'],
            ]);
        }
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors()->toArray();
        $flattenedErrors = [];

        foreach ($errors as $key => $messages) {
            $newKey = str_contains($key, '.') ? substr($key, 0, strpos($key, '.')) : $key;
            if (!isset($flattenedErrors[$newKey])) {
                $flattenedErrors[$newKey] = $messages;
            } else {
                $flattenedErrors[$newKey] = array_merge($flattenedErrors[$newKey], $messages);
            }
        }

        throw new HttpResponseException(response()->json([
            'message' => 'Заполните форму правильно',
            'errors' => $flattenedErrors
        ], Response::HTTP_UNPROCESSABLE_ENTITY));
    }
}
