<?php

namespace App\Services\ImportTransactions;

use App\Models\Account;
use App\Services\ServiceInstance;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ParseTochka extends Parser implements ParserContract
{
    use ServiceInstance;

    const CATEGORY_PARENT = 'ИП';

    /**
     * Row map:
     * $row[3] = 'Дата операции'
     * $row[18] = 'Сумма списания'
     * $row[19] = 'Сумма зачисления'
     * $row[20] = 'Дочерняя категория'
     */
    public function parse(array $row, Account $account, ?Carbon $latestImportedDate): ?Collection
    {
        if (!$dateRow = $row[3]) {
            return null;
        }

        $date = $this->getDate($dateRow);
        if(!$date || $this->isImportedAlready($date, $latestImportedDate)) {
            return null;
        }

        $amount = $row[19] ?: '-'.$row[18];

        $childCategoryName = $this->getCategoryName($row[20], false);
        $parentCategoryName = $this->getCategoryName(self::CATEGORY_PARENT, true, $childCategoryName);

        return new Collection([
            'date' => $date,
            'amount' => $this->getAmount($amount),
            'parent_category_name' => $parentCategoryName,
            'child_category_name' => $childCategoryName,
        ]);
    }
}
