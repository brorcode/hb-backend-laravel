<?php

namespace App\Http\Requests\Api\v1\Tag;

use App\Http\Requests\Api\v1\ApiRequest;
use App\Models\Tag;
use Illuminate\Validation\Rule;

/**
 * @property-read Tag|null tag
 *
 * @property-read string name
 */
class TagUpsertRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                Rule::unique((new Tag())->getTable())->ignore($this->tag),
            ],
        ];
    }
}
