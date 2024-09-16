<?php

namespace App\Http\Requests\Api\v1\Transaction;

use App\Http\Requests\Api\v1\ApiRequest;

/**
 * @property-read array<int, int> selected_items
 */
class TransactionDestroyManyRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'selected_items' => ['required', 'array'],
        ];
    }
}
