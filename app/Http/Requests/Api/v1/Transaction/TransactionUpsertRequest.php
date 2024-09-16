<?php

namespace App\Http\Requests\Api\v1\Transaction;

use App\Http\Requests\Api\v1\ApiRequest;
use App\Models\Account;
use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

/**
 * @property-read string amount
 * @property-read int category_id
 * @property-read int account_id
 * @property-read Carbon created_at
 * @property-read bool is_debit
 * @property-read bool is_transfer
 */
class TransactionUpsertRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:0.01'],
            'category_id' => ['required', Rule::exists((new Category())->getTable(), (new Category())->getKeyName())],
            'account_id' => ['required', Rule::exists((new Account())->getTable(), (new Account())->getKeyName())],
            'created_at' => ['required', 'date'],
            'is_debit' => ['required', 'bool'],
            'is_transfer' => ['required', 'bool'],
        ];
    }
}
