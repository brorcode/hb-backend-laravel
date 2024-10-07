<?php

namespace App\Http\Requests\Api\v1\Transaction;

use App\Http\Requests\Api\v1\ApiRequest;
use App\Models\Account;
use App\Models\Category;
use App\Rules\ExistForUserRule;

/**
 * @property-read string amount
 * @property-read int category_id
 * @property-read int account_id
 * @property-read string created_at
 * @property-read bool is_debit
 * @property-read bool is_transfer
 */
class TransactionUpsertRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:0.01'],
            'category_id' => ['required', new ExistForUserRule(Category::class)],
            'account_id' => ['required', new ExistForUserRule(Account::class)],
            'created_at' => ['required', 'date'],
            'is_debit' => ['required', 'bool'],
            'is_transfer' => ['required', 'bool'],
        ];
    }
}
