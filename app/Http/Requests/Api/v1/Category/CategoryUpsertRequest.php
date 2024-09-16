<?php

namespace App\Http\Requests\Api\v1\Category;

use App\Http\Requests\Api\v1\ApiRequest;

/**
 * @property-read string name
 */
class CategoryUpsertRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required'],
        ];
    }
}
