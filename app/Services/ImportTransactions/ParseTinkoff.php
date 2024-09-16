<?php

namespace App\Services\ImportTransactions;

use App\Models\Account;
use App\Services\ServiceInstance;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ParseTinkoff extends Parser implements ParserContract
{
    use ServiceInstance;

    const STATUS_FAILED = 'FAILED';

    /**
     * Row map:
     * $row[0] = 'Дата операции'
     * $row[2] = 'Номер карты'
     * $row[3] = 'Статус'
     * $row[6] = 'Сумма платежа'
     * $row[9] = 'Родительская категория'
     * $row[11] = 'Дочерняя категория'
     */
    public function parse(array $row, Account $account, ?Carbon $latestImportedDate): ?Collection
    {
        $row = $this->trimValues($row);

        if (!$dateRow = $row[0]) {
            return null;
        }

        // @todo temp solution if I need to import child account. Tinkoff joined accounts for import
        // if ($row[2] !== '*4514') {
        //     return null;
        // }

        // @todo temp solution because Tinkoff joined accounts for import
        if ($account->getKey() === 1 && $row[2] === '*4514') {
            return null;
        }

        $date = $this->getDate($dateRow);
        if (!$date || !$this->isCompleted($row[3]) || $this->isImportedAlready($date, $latestImportedDate)) {
            return null;
        }

        $childCategoryName = $this->getCategoryName($row[11], false);
        $parentCategoryName = $this->getCategoryName($row[9], true, $childCategoryName);

        return new Collection([
            'date' => $date,
            'amount' => $this->getAmount($row[6]),
            'parent_category_name' => $parentCategoryName,
            'child_category_name' => $childCategoryName,
        ]);
    }

    private function isCompleted(string $status): bool
    {
        return !($status === self::STATUS_FAILED);
    }
}
