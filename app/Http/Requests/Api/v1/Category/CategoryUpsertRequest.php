<?php

namespace App\Http\Requests\Api\v1\Category;

use App\Http\Requests\Api\v1\ApiRequest;
use App\Models\Category;
use App\Rules\ExistForUserRule;
use App\Rules\UniqueNameForUserRule;

/**
 * @property-read Category|null category
 *
 * @property-read string name
 * @property-read int|null parent_id
 * @property-read bool is_child
 */
class CategoryUpsertRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                new UniqueNameForUserRule(Category::class, $this->category),
            ],
            'parent_id' => [
                'required_if:is_child,true',
                new ExistForUserRule(Category::class),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'parent_id.required_if' => 'Поле :attribute обязательно.',
        ];
    }
}
