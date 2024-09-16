<?php

namespace App\Http\Requests\Api\v1;

use Illuminate\Foundation\Http\FormRequest;

class ApiRequest extends FormRequest
{
    /**
     * If you disable instant validate, then you must call ->validate() manually
     */
    protected bool $instantValidate = true;

    public function rules(): array
    {
        return [];
    }

    public function rulesPassed(): void
    {
    }

    final public function validate(): void
    {
        parent::validateResolved();

        $this->rulesPassed();
    }

    public function validateResolved(): void
    {
        if ($this->instantValidate) {
            $this->validate();
        }
    }
}
