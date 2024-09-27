<?php

namespace Tests\Unit\ImportTransactions;

use App\Models\Account;
use App\Models\Category;
use App\Models\CategoryPointer;
use App\Models\CategoryPointerTag;
use App\Models\Integration;
use App\Models\Transaction;
use App\Services\ImportTransactions\ImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ImportTransactionsTinkoffTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->userLogin();

        Integration::factory()
            ->count(5)
            ->sequence(
                ['code_id' => Integration::CODE_ID_TINKOFF_BANK],
                ['code_id' => Integration::CODE_ID_SBERBANK],
                ['code_id' => Integration::CODE_ID_TOCHKA_BANK],
                ['code_id' => Integration::CODE_ID_YANDEX_MONEY],
                ['code_id' => Integration::CODE_ID_TINKOFF_BANK_BUSINESS],
            )
            ->create()
        ;
    }

    public function testFileUploadCreatesCorrectDebitCreditTransferTransactions(): void
    {
        CategoryPointerTag::factory()->for(
            CategoryPointer::factory()->isParent(true)
        )->create();
        $account = Account::factory([
            'integration_id' => Integration::findTinkoffBank()->getKey(),
        ])->create();
        $file = UploadedFile::fake()->createWithContent(
            'transactions.csv',
            $this->createFileContentTinkoff()
        );

        $this->assertSame(0, Transaction::query()->count());
        $this->assertSame(0, Category::query()->count());
        $service = ImportService::create();
        $service->handle($file, $account);
        $imported = $service->getImportedCount();

        $this->assertSame(6, Category::query()->count());
        $this->assertDatabaseHas((new Category())->getTable(), [
            'name' => 'Доход',
        ]);
        $this->assertDatabaseHas((new Category())->getTable(), [
            'name' => 'Доходы',
        ]);

        $this->assertSame(4, $imported);
        $this->assertSame(4, Transaction::query()->count());
        $this->assertSame(1, Transaction::query()
            ->where('is_transfer', true)
            ->where('is_debit', true)
            ->count())
        ;
        $this->assertSame(1, Transaction::query()
            ->where('is_transfer', true)
            ->where('is_debit', false)
            ->count())
        ;
        $this->assertSame(1, Transaction::query()
            ->where('is_transfer', false)
            ->where('is_debit', true)
            ->count())
        ;
        $this->assertSame(1, Transaction::query()
            ->where('is_transfer', false)
            ->where('is_debit', false)
            ->count())
        ;
    }

    public function testFileUploadCreatesCorrectCreditTransferTransactionsForCash(): void
    {
        $account = Account::factory([
            'integration_id' => Integration::findTinkoffBank()->getKey(),
        ])->create();
        $accountCash = Account::factory([
            'name' => ImportService::CASH,
            'integration_id' => null,
        ])->create();
        $file = UploadedFile::fake()->createWithContent(
            'transactions.csv',
            $this->createFileContentTinkoffCash()
        );

        $this->assertSame(0, Transaction::query()->count());
        $service = ImportService::create();
        $service->handle($file, $account);
        $imported = $service->getImportedCount();

        $this->assertSame(4, $imported);
        $this->assertSame(4, Transaction::query()->count());
        $this->assertSame(2, Transaction::query()->where('account_id', $account->getKey())->count());
        $this->assertSame(2, Transaction::query()->where('account_id', $accountCash->getKey())->count());
        $this->assertSame(2, Transaction::query()
            ->where('account_id', $account->getKey())
            ->where('is_transfer', true)
            ->where('is_debit', false)
            ->count())
        ;
        $this->assertSame(2, Transaction::query()
            ->where('account_id', $accountCash->getKey())
            ->where('is_transfer', true)
            ->where('is_debit', true)
            ->count())
        ;
    }

    public function testFileUploadDoesNotImportTwiceSameTransactionsBasedOnDateChecking(): void
    {
        $account = Account::factory([
            'integration_id' => Integration::findTinkoffBank()->getKey(),
        ])->create();

        $this->assertSame(0, Transaction::query()->count());
        $file = UploadedFile::fake()->createWithContent(
            'transactions.csv',
            $this->createFileContentTinkoff()
        );

        $service = ImportService::create();
        $service->handle($file, $account);
        $imported = $service->getImportedCount();

        $this->assertSame(4, $imported);
        $this->assertCount(4, Transaction::all());

        $file = UploadedFile::fake()->createWithContent(
            'transactions.csv',
            $this->createFileContentTinkoff()
        );
        $service->handle($file, $account);
        $imported = $service->getImportedCount();
        $this->assertSame(0, $imported);
        $this->assertCount(4, Transaction::all());
    }

    public function testImportCanUseNewParentCategoryNameByCategoryPointer(): void
    {
        CategoryPointerTag::factory([
            'name' => 'Child category name',
        ])->for(
            CategoryPointer::factory([
                'name' => $newCategoryName = 'New Parent category name',
            ])->isParent(true)
        )->create();

        $account = Account::factory([
            'integration_id' => Integration::findTinkoffBank()->getKey(),
        ])->create();

        $file = UploadedFile::fake()->createWithContent(
            'transactions.csv',
            $this->createFileContentTinkoffToRenameCategory()
        );

        $this->assertSame(0, Category::query()->count());
        $this->assertSame(0, Transaction::query()->count());
        $service = ImportService::create();
        $service->handle($file, $account);

        $this->assertSame(2, Category::query()->count());
        $this->assertDatabaseMissing((new Category())->getTable(), [
            'name' => 'Parent category name',
        ]);
        $this->assertDatabaseHas((new Category())->getTable(), [
            'name' => $newCategoryName,
            'parent_id' => null,
        ]);
    }

    public function testImportCanUseNewChildCategoryNameByCategoryPointer(): void
    {
        CategoryPointerTag::factory([
            'name' => $oldCategoryName = 'Child category name',
        ])->for(
            CategoryPointer::factory([
                'name' => $newCategoryName = 'New Child category name',
            ])->isParent(false)
        )->create();

        $account = Account::factory([
            'integration_id' => Integration::findTinkoffBank()->getKey(),
        ])->create();

        $file = UploadedFile::fake()->createWithContent(
            'transactions.csv',
            $this->createFileContentTinkoffToRenameCategory()
        );

        $this->assertSame(0, Category::query()->count());
        $this->assertSame(0, Transaction::query()->count());
        $service = ImportService::create();
        $service->handle($file, $account);

        $this->assertSame(2, Category::query()->count());
        $this->assertDatabaseMissing((new Category())->getTable(), [
            'name' => $oldCategoryName,
        ]);
        $this->assertDatabaseHas((new Category())->getTable(), [
            'name' => $newCategoryName,
        ]);
    }

    /**
     * Transactions rows
     * 1 -> transfer/debit
     * 2 -> transfer/credit
     * 3 -> debit
     * 4 -> credit
     */
    private function createFileContentTinkoff(): string
    {
        $rows = [
            'Дата операции;1;2;Статус;4;5;Сумма платежа;7;8;Категория;10;Описание;12',
            '01.01.2020 00:00:01;1;2;OK;4;5;100,00;6;8;Переводы между счетами;10;Перевод;12',
            '01.01.2020 00:00:02;1;2;OK;4;5;-100,00;6;8;Переводы между счетами;10;Перевод;12',
            '01.01.2020 00:00:03;1;2;OK;4;5;100,00;6;8;Доходы;10;Доход;12',
            '01.01.2020 00:00:04;1;2;OK;4;5;-100,00;6;8;Расходы;10;Расход;12',
        ];

        return implode("\n", $rows);
    }

    /**
     * Transactions rows
     * 1 -> transfer/credit
     * 2 -> transfer/credit
     */
    private function createFileContentTinkoffCash(): string
    {
        $rows = [
            'Дата операции;1;2;Статус;4;5;Сумма платежа;7;8;Категория;10;Описание;12',
            '01.01.2020 00:00:01;1;2;OK;4;5;-100,00;6;8;Наличные;10;Банкомат 1;12',
            '01.01.2020 00:00:01;1;2;OK;4;5;-100,00;6;8;Наличные;10;Банкомат 2;12',
        ];

        return implode("\n", $rows);
    }

    private function createFileContentTinkoffToRenameCategory(): string
    {
        $rows = [
            'Дата операции;1;2;Статус;4;5;Сумма платежа;7;8;Категория;10;Описание;12',
            '01.01.2020 00:00:01;1;2;OK;4;5;100,00;6;8;Parent category name;10;Child category name;12',
        ];

        return implode("\n", $rows);
    }
}
