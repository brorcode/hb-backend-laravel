<?php

namespace App\Services\ImportTransactions;

use App\Exceptions\SystemException;
use App\Models\Account;
use App\Services\ServiceInstance;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class ParseTinkoffBusiness extends Parser implements ParserContract
{
    use ServiceInstance;

    const CATEGORY_PARENT = 'ИП';

    /**
     * Row map:
     * $row[2] = 'Тип операции (пополнение/списание)'
     * $row[7] = 'Дата операции'
     * $row[11] = 'Сумма платежа'
     * $row[19] = 'Дочерняя категория'
     *
     * @throws SystemException
     */
    public function parse(array $row, Account $account, ?Carbon $latestImportedDate): ?Collection
    {
        if (!$dateRow = $row[7]) {
            return null;
        }

        $date = $this->getDate($dateRow, 'd/m/Y');

        if(!$date || $this->isImportedAlready($date, $latestImportedDate)) {
            return null;
        }

        $row[11] = str_replace(',', '.', $row[11]);
        $amountInCents = strval($row[11] * 100);

        $amount = match($row[2]) {
            'Debit' => $amountInCents,
            'Credit' => '-'.$amountInCents,
            default => throw new SystemException('Undefined operation type'),
        };

        $childCategoryName = $this->getCategoryName($row[19], false);
        $parentCategoryName = $this->getCategoryName(self::CATEGORY_PARENT, true, $childCategoryName);

        return new Collection([
            'date' => $date,
            'amount' => $this->getAmount($amount),
            'parent_category_name' => $parentCategoryName,
            'child_category_name' => $childCategoryName,
        ]);
    }
}
