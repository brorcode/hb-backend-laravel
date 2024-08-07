<?php

namespace App\Http\Requests\Api\v1;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @property-read Category|null user
 *
 * @property-read string name
 * @property-read string description
 */
class CategoryUpsertRequest extends FormRequest
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
            'name' => ['required'],
            'description' => ['required'],
        ];
    }
}
