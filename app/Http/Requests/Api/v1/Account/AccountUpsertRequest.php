<?php

namespace App\Http\Requests\Api\v1\Account;

use App\Http\Requests\Api\v1\ApiRequest;
use App\Models\Account;
use App\Rules\UniqueNameForUserRule;

/**
 * @property-read Account|null account
 *
 * @property-read string name
 */
class AccountUpsertRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                new UniqueNameForUserRule(Account::class, $this->account),
            ],
        ];
    }
}
