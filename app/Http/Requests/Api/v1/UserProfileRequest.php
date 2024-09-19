<?php

namespace App\Http\Requests\Api\v1;

use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * @property-read string name
 * @property-read string email
 * @property-read string|null password
 */
class UserProfileRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required'],
            'email' => [
                'required',
                'email:filter',
                Rule::unique((new User())->getTable())->ignore($this->user()),
            ],
            'password' => ['nullable', 'confirmed', Password::defaults()],
        ];
    }
}
