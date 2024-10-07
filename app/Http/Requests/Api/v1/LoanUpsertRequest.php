<?php

namespace App\Http\Requests\Api\v1;

use App\Models\Loan;
use Illuminate\Validation\Rule;

/**
 * @property-read Loan|null account
 *
 * @property-read string name
 * @property-read int type_id
 * @property-read string amount
 * @property-read string deadline_on
 */
class LoanUpsertRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required'],
            'type_id' => ['required', Rule::in(array_keys(Loan::TYPES))],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'deadline_on' => ['required', 'date'],
        ];
    }
}
