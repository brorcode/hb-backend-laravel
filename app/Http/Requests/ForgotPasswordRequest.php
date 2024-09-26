<?php

namespace App\Http\Requests;

use App\Http\Requests\Api\v1\ApiRequest;
use App\Models\Account;
use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * @property-read string email
 */
class ForgotPasswordRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'string',
                'lowercase',
                'email:filter',
                'max:255',
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'email' => 'Email',
        ];
    }
}
