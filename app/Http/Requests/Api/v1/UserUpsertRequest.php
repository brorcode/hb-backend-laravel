<?php

namespace App\Http\Requests\Api\v1;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * @property-read User|null user
 *
 * @property-read string name
 * @property-read string email
 * @property-read string|null password
 */
class UserUpsertRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required'],
            'email' => [
                'required',
                'email:filter',
                Rule::unique((new User())->getTable())->ignore($this->user),
            ],
            'password' => [Password::min(8)],
        ];
    }
}
