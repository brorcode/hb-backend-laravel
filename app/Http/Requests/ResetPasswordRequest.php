<?php

namespace App\Http\Requests;

use App\Http\Requests\Api\v1\ApiRequest;
use App\Models\Account;
use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * @property-read string email
 * @property-read string token
 * @property-read string password
 */
class ResetPasswordRequest extends ApiRequest
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
            'token' => ['required'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'Имя',
            'email' => 'Email',
            'password' => 'Пароль',
        ];
    }
}
