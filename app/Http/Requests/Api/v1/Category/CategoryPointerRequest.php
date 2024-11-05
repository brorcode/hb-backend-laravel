<?php

namespace App\Http\Requests\Api\v1\Category;

use App\Http\Requests\Api\v1\ApiRequest;

/**
 * @property-read array $parent
 * @property-read array $child
 */
class CategoryPointerRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'parent' => ['required', 'array'],
            'child' => ['required', 'array'],
            'parent.*.name' => ['required'],
            'child.*.name' => ['required'],
            'parent.*.tags_array' => ['required', 'array'],
            'child.*.tags_array' => ['required', 'array'],
        ];
    }
}
