<?php

namespace App\Services\ImportTransactions;

use App\Exceptions\SystemException;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Services\ServiceInstance;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class ImportService
{
    use ServiceInstance;

    const BETWEEN_ACCOUNTS = 'Переводы между счетами';
    const CASH = 'Наличные';
    const CORRECTING = 'Балансовые транзакции';

    const TRANSFER = [
        self::BETWEEN_ACCOUNTS,
        self::CASH,
        self::CORRECTING,
    ];

    private ReaderFactory $factory;
    private int $imported;

    public function __construct(ReaderFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * @throws SystemException
     */
    public function handle(UploadedFile $file, Account $account): void
    {
        $this->imported = 0;
        $reader = $this->factory->make($account);
        $transactions = $reader->parse($file);

        try {
            $this->saveTransactions($transactions, $account);
        } catch (Exception $e) {
            Log::error($e->getMessage() . "\n" . $e->getTraceAsString());

            throw new SystemException($e->getMessage());
        }
    }

    private function saveTransactions(Collection $rows, Account $account): void
    {
        $sorted = $rows->sortBy('date');
        $sorted->each(function (Collection $row) use ($account) {
            $this->populateDatabase($row, $account);

            if ($row['parent_category_name'] === self::CASH) {
                $this->createCashCreditTransaction($row);
            }
        });
    }

    private function populateDatabase(Collection $row, Account $account): void
    {
        $category = $this->getCategory($row['parent_category_name'], $row['child_category_name']);

        $transaction = new Transaction();
        $transaction->category_id = $category->getKey();
        $transaction->amount = $row['amount'];
        $transaction->account_id = $account->getKey();
        $transaction->is_debit = $this->isDebit($row['amount']);
        $transaction->is_transfer = $this->isBetweenAccounts($row['parent_category_name']);
        $transaction->created_at = $row['date'];
        $transaction->updated_at = $row['date'];
        $transaction->save();
        
        $this->imported++;
    }

    public function getCategory(string $parentCategoryName, string $childCategoryName): Category
    {
        if (!$parentCategory = Category::findByName($parentCategoryName)) {
            $parentCategory = new Category();
            $parentCategory->name = $parentCategoryName;
            $parentCategory->save();
        }

        if (!$category = Category::findByName($childCategoryName)) {
            $category = new Category();
            $category->parent_id = $parentCategory->getKey();
            $category->name = $childCategoryName;
            $category->save();
        }

        if ($parentCategory->getKey() !== $category->parent_id) {
            $category->parent_id = $parentCategory->getKey();
            $category->save();
        }

        return $category;
    }

    private function isDebit(string $amount): bool
    {
        return $amount < 0;
    }

    public function isBetweenAccounts(string $parentCategoryName): bool
    {
        if (in_array($parentCategoryName, self::TRANSFER)) {
            return true;
        }

        return false;
    }

    private function createCashCreditTransaction(Collection $row): void
    {
        /** @var Account $account */
        if (!$account = Account::query()->where('name', self::CASH)->first()) {
            return;
        }

        $row['amount'] = abs($row['amount']);
        $row['date'] = Carbon::parse($row['date'])->addSecond()->toDateTimeString();
        $this->populateDatabase($row, $account);
    }

    public function getImportedCount(): int
    {
        return $this->imported;
    }
}
