<?php

namespace Tests\Unit\ImportTransactions;

use App\Exceptions\SystemException;
use App\Models\Account;
use App\Models\Integration;
use App\Models\Transaction;
use App\Services\ImportTransactions\ImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ImportTransactionsYandexTest extends TestCase
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
        $account = Account::factory([
            'integration_id' => Integration::findYandexMoney()->getKey(),
        ])->create();
        $file = UploadedFile::fake()->createWithContent(
            'transactions.csv',
            $this->createFileContentYandex()
        );

        $this->assertSame(0, Transaction::query()->count());

        $this->expectException(SystemException::class);
        $this->expectExceptionMessage('Автоимпорт для Яндекса отключен, внесите транзакции вручную.');
        $service = ImportService::create();
        $service->handle($file, $account);

        $this->assertSame(0, Transaction::query()->count());

        // $imported = $service->getImportedCount();
        // $this->assertSame(4, $imported);
        // $this->assertSame(4, Transaction::query()->count());
        // $this->assertSame(2, Transaction::query()
        //     ->where('is_transfer', false)
        //     ->where('is_debit', true)
        //     ->count())
        // ;
        // $this->assertSame(2, Transaction::query()
        //     ->where('is_transfer', false)
        //     ->where('is_debit', false)
        //     ->count())
        // ;
    }

    // public function testFileUploadCreatesExtraCreditTransferTransaction(): void
    // {
    //     $account = Account::factory([
    //         'integration_id' => Integration::findYandexMoney()->getKey(),
    //         ])
    //         ->create()
    //     ;
    //     $file = UploadedFile::fake()->createWithContent(
    //         'transactions.csv',
    //         $this->createFileContentYandexWithOneCredit()
    //     );
    //
    //     $this->assertSame(0, Transaction::query()->count());
    //     $service = ImportService::create();
    //     $service->handle($file, $account);
    //     $imported = $service->getImportedCount();
    //
    //     $this->assertSame(2, $imported);
    //     $this->assertSame(2, Transaction::query()->count());
    //     $this->assertSame(1, Transaction::query()
    //         ->where('is_transfer', false)
    //         ->where('is_debit', true)
    //         ->count())
    //     ;
    //     $this->assertSame(1, Transaction::query()
    //         ->where('is_transfer', true)
    //         ->where('is_debit', false)
    //         ->count())
    //     ;
    // }

    /**
     * Transactions rows
     * 1 -> transfer/credit
     * 2 -> transfer/debit
     * 3 -> credit
     * 4 -> debit
     */
    private function createFileContentYandex(): string
    {
        $rows = [
            '+/-;дата;сумма;3;статус;название',
            '+;01.01.2020 00:00:01;100,00;3;;Переводы между счетами',
            '-;01.01.2020 00:00:01;100,00;3;;Переводы между счетами',
            '+;01.01.2020 00:00:01;100,00;3;;Доходы',
            '-;01.01.2020 00:00:01;100,00;3;;Расходы',
        ];

        return implode("\n", $rows);
    }

    /**
     * Transactions rows
     * 1 -> credit
     *
     */
    private function createFileContentYandexWithOneCredit(): string
    {
        $rows = [
            '+/-;дата;сумма;3;статус;название',
            '-;01.01.2020 00:00:01;100,00;3;;Расходы',
        ];

        return implode("\n", $rows);
    }
}
