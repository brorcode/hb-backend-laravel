<?php

namespace App\Http\Requests\Api\v1\Category;

use App\Http\Requests\Api\v1\ApiRequest;
use App\Models\Category;
use Illuminate\Validation\Rule;

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
                // TODO does this checks all categories in DB or only for the user? It should check only for the logged in user
                Rule::unique((new Category())->getTable())->ignore($this->category),
            ],
            'parent_id' => [
                'required_if:is_child,true',
                Rule::exists((new Category())->getTable(), (new Category())->getKeyName()),
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
