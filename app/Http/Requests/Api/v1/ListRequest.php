<?php

namespace App\Http\Requests\Api\v1;

use App\Exceptions\ApiBadRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @property-read int page
 * @property-read int limit
 * @property-read array sorting
 * @property-read array filters
 */
class ListRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
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
        throw new ApiBadRequest();
    }
}
