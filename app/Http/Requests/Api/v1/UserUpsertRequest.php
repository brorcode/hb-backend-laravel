<?php

namespace App\Http\Requests\Api\v1;

use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * @property-read User|null user
 *
 * @property-read string name
 * @property-read string email
 * @property-read string|null password
 */
class UserUpsertRequest extends ApiRequest
{
    public function rules(): array
    {
        $passwordRules = ['nullable', 'confirmed', Password::defaults()];
        if (!$this->user) {
            $passwordRules[0] = 'required';
        }

        return [
            'name' => ['required'],
            'email' => [
                'required',
                'email:filter',
                Rule::unique((new User())->getTable())->ignore($this->user),
            ],
            'password' => $passwordRules,
        ];
    }
}
