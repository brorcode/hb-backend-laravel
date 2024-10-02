<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Model;

/**
 * @template TModel of Model
 */
class UniqueNameForUserRule implements ValidationRule
{
    /**
     * @var class-string<Model|TModel>
     */
    private string $modelClass;
    private ?Model $ignoreModel;

    public function __construct(string $modelClass, ?Model $ignoreModel)
    {
        $this->modelClass = $modelClass;
        $this->ignoreModel = $ignoreModel;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $builder = $this->modelClass::query()->where('name', $value);

        if ($this->ignoreModel) {
            $builder->whereNot((new $this->modelClass())->getKeyName(), $this->ignoreModel->getKey());
        }

        if ($builder->exists()) {
            $fail('Такое название уже существует.');
        }
    }
}
