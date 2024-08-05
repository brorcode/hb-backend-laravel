<?php

namespace App\Http\Requests\Api\v1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @property-read int page
 * @property-read int limit
 * @property-read array sorting
 */
class UserRequest extends FormRequest
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
            'sorting' => ['required', 'array'],
            'sorting.column' => ['nullable', 'string'],
            'sorting.direction' => ['nullable', 'string'],
        ];
    }

    public function getSortingColumn(): ?string
    {
        return strtolower(
            preg_replace('/(?<!^)[A-Z]/', '_$0',
            $this->sorting['column'])
        ) ?? null;
    }

    public function getSortingDirection(): ?string
    {
        return $this->sorting['direction'] ?? null;
    }
}
