<?php

namespace App\Http\Requests\Api\v1;

use App\Models\Loan;
use Illuminate\Validation\Rule;

/**
 * @property-read int|null months
 */
class DashboardRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'months' => ['integer'],
        ];
    }
}
