<?php

namespace App\Http\Requests\Api\v1\Category;

use App\Http\Requests\Api\v1\ApiRequest;
use App\Models\Category;
use Illuminate\Validation\Rule;

/**
 * @property-read Category|null category
 *
 * @property-read string name
 */
class CategoryUpsertRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                Rule::unique((new Category())->getTable())->ignore($this->category),
            ],
        ];
    }
}
