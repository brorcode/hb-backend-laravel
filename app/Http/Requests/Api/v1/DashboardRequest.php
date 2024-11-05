<?php

namespace App\Http\Requests\Api\v1;

use App\Models\Loan;
use Illuminate\Validation\Rule;

/**
 * @property-read int|null $months
 * @property-read int|null $category_count
 */
class DashboardRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'months' => ['integer'],
            'category_count' => ['integer'],
        ];
    }
}
