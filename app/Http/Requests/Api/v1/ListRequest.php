<?php

namespace App\Http\Requests\Api\v1;

use App\Exceptions\ApiBadRequest;
use Illuminate\Contracts\Validation\Validator;

/**
 * @property-read int page
 * @property-read int limit
 * @property-read array sorting
 * @property-read array filters
 */
class ListRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'page' => ['required', 'integer'],
            'limit' => ['required', 'integer'],
            'sorting' => ['nullable', 'array'],
            'sorting.column' => ['required_with:sorting', 'string'],
            'sorting.direction' => ['required_with:sorting', 'in:ASC,DESC'],
            'filters' => ['nullable', 'array'],
        ];
    }

    public function getSortingColumn(): string
    {
        return strtolower(
            preg_replace('/(?<!^)[A-Z]/', '_$0',
            $this->sorting['column'])
        );
    }

    public function getSortingDirection(): string
    {
        return $this->sorting['direction'];
    }

    /**
     * @throws ApiBadRequest
     */
    protected function failedValidation(Validator $validator): void
    {
        $exception = $validator->getException();
        $instance = new $exception($validator);

        logger()->error($instance->getMessage(), [
            'errors' => $instance->errors(),
        ]);

        throw new ApiBadRequest();
    }
}
