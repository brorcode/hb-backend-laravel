<?php

namespace App\Services\ImportTransactions;

use App\Exceptions\SystemException;
use App\Http\Requests\Api\v1\Account\AccountTransactionsImportRequest;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\TransactionsImport;
use App\Models\User;
use App\Services\OwnerService;
use App\Services\ServiceInstance;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

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
    private User $user;
    private NotificationService $notificationService;

    /**
     * @throws SystemException
     */
    public function __construct(ReaderFactory $factory)
    {
        $this->factory = $factory;
        $this->user = OwnerService::make()->getUser();
        $this->notificationService = NotificationService::make();
    }

    /**
     * @throws SystemException
     */
    public function handle(string $filePath, Account $account): void
    {
        $this->imported = 0;
        $reader = $this->factory->make($account);
        $transactions = $reader->parse($filePath);
        $this->saveTransactions($transactions, $account);
    }

    private function saveTransactions(Collection $rows, Account $account): void
    {
        $countTransactions = $rows->count();
        $this->notificationService->addMessage($this->user, "Общее количество транзакций для импорта {$countTransactions}");

        $sorted = $rows->sortBy('date');
        $sorted->each(function (Collection $row) use ($account, $countTransactions) {
            $this->populateDatabase($row, $account);

            if ($row['parent_category_name'] === self::CASH) {
                $this->createCashDebitTransaction($row);
            }

            if ($this->imported % 100 === 0) {
                $this->notificationService->addMessage($this->user, "Импортировано {$this->imported} транзакций из {$countTransactions}");
            }
        });

        $this->notificationService->addMessage($this->user, "Импортировано {$this->imported} транзакций из {$countTransactions}");
    }

    private function populateDatabase(Collection $row, Account $account): void
    {
        $category = $this->getCategory($row['parent_category_name'], $row['child_category_name']);

        $transaction = new Transaction();
        $transaction->category_id = $category->getKey();
        $transaction->amount = $row['amount'] * 100;
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
        return $amount > 0;
    }

    public function isBetweenAccounts(string $parentCategoryName): bool
    {
        if (in_array($parentCategoryName, self::TRANSFER)) {
            return true;
        }

        return false;
    }

    private function createCashDebitTransaction(Collection $row): void
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

    public function canRunImport(): bool
    {
        return TransactionsImport::query()
            ->where('user_id', Auth::id())
            ->where('status_id', TransactionsImport::STATUS_ID_PROCESS)
            ->doesntExist()
        ;
    }

    public function createTransactionsImport(AccountTransactionsImportRequest $request): TransactionsImport
    {
        /** @var User $user */
        $user = Auth::user();
        $now = Carbon::now();
        $filePath = $request->file->storeAs(
            "transactions_imports/{$user->getKey()}",
            "{$now->toDateTimeString()}-{$request->file->getClientOriginalName()}"
        );

        $transactionsImport = new TransactionsImport();
        $transactionsImport->user_id = $user->getKey();
        $transactionsImport->account_id = $request->account->getKey();
        $transactionsImport->status_id = TransactionsImport::STATUS_ID_PROCESS;
        $transactionsImport->file_name = $request->file->getClientOriginalName();
        $transactionsImport->file_path = $filePath;
        $transactionsImport->started_at = $now;
        $transactionsImport->save();

        return $transactionsImport;
    }
}
