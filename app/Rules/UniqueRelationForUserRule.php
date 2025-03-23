<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Model;

/**
 * @template TModel of Model
 */
class UniqueRelationForUserRule implements ValidationRule
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
        $builder = $this->modelClass::query()->where($attribute, $value);

        if ($builder->exists()) {
            $fail('Такое значение уже существует.');
        }
    }
}
