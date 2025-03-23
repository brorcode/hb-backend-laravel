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
    private ?Model $ignoredModel;

    public function __construct(string $modelClass, ?Model $ignoredModel)
    {
        $this->modelClass = $modelClass;
        $this->ignoredModel = $ignoredModel;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $builder = $this->modelClass::query()
            ->where($attribute, $value)
        ;

        if ($this->ignoredModel) {
            $builder->where('id', '!=', $this->ignoredModel->getKey());
        }

        if ($builder->exists()) {
            $fail('Такое значение уже существует.');
        }
    }
}
