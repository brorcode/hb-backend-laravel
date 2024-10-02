<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Model;

/**
 * @template TModel of Model
 */
class ExistForUserRule implements ValidationRule
{
    /**
     * @var class-string<Model|TModel>
     */
    private string $modelClass;

    public function __construct(string $modelClass)
    {
        $this->modelClass = $modelClass;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $exists = $this->modelClass::query()->where((new $this->modelClass())->getKeyName(), $value)->exists();

        if (!$exists) {
            $fail('Такого значения не существует.');
        }
    }
}
