<?php

namespace App\Http\Requests\Api\v1\Tag;

use App\Http\Requests\Api\v1\ApiRequest;

/**
 * @property-read string name
 */
class TagUpsertRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required'],
        ];
    }
}
