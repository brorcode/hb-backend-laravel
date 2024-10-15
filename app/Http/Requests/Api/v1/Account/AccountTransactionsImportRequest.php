<?php

namespace App\Http\Requests\Api\v1\Account;

use App\Http\Requests\Api\v1\ApiRequest;
use App\Models\Account;
use App\Services\ImportTransactions\ImportService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

/**
 * @property-read int|null account_id
 * @property-read UploadedFile file
 */
class AccountTransactionsImportRequest extends ApiRequest
{
    public Account $account;

    public function rules(): array
    {
        return [
            'file' => ['required', 'file'],
        ];
    }

    /**
     * @throws ValidationException
     */
    public function rulesPassed(): void
    {
        try {
            $this->account = Account::findOrFail($this->account_id);
        } catch (ModelNotFoundException) {
            throw ValidationException::withMessages([
                'account_id' => 'Аккаунт не найден.',
            ]);
        }

        if (!ImportService::create()->canRunImport()) {
            throw ValidationException::withMessages([
                'file' => 'Импорт уже запущен. Дождидесь завершения.',
            ]);
        }
    }
}
