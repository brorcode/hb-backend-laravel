<?php

namespace App\Http\Requests\Api\v1\Account;

use App\Http\Requests\Api\v1\ApiRequest;

/**
 * @property-read string name
 */
class AccountUpsertRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required'],
        ];
    }
}
