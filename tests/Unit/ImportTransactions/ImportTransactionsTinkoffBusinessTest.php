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

class ImportTransactionsTinkoffBusinessTest extends TestCase
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
            'integration_id' => Integration::findTinkoffBankBusiness()->getKey(),
        ])->create();
        $file = UploadedFile::fake()->createWithContent(
            'transactions.csv',
            $this->createFileContentTinkoffBusiness()
        );

        $this->assertSame(0, Transaction::query()->count());
        $service = ImportService::create();
        $service->handle($file, $account);
        $imported = $service->getImportedCount();

        $this->assertSame(4, $imported);
        $this->assertSame(4, Transaction::query()->count());
        $this->assertSame(2, Transaction::query()
            ->where('is_transfer', false)
            ->where('is_debit', true)
            ->count())
        ;
        $this->assertSame(2, Transaction::query()
            ->where('is_transfer', false)
            ->where('is_debit', false)
            ->count())
        ;
    }

    public function testFileUploadThrowExceptionWhenWrongTransactionType(): void
    {
        $account = Account::factory([
            'integration_id' => Integration::findTinkoffBankBusiness()->getKey(),
        ])->create();
        $file = UploadedFile::fake()->createWithContent(
            'transactions.csv',
            $this->createFileContentTinkoffBusinessWithWrongTransactionType()
        );

        $this->expectException(SystemException::class);
        $this->expectExceptionMessage('Undefined operation type');

        $this->assertSame(0, Transaction::query()->count());
        $service = ImportService::create();
        $service->handle($file, $account);
        $imported = $service->getImportedCount();

        $this->assertSame(0, $imported);
        $this->assertSame(0, Transaction::query()->count());
    }

    /**
     * Transactions rows
     * 1 -> transfer/credit
     * 2 -> transfer/debit
     * 3 -> credit
     * 4 -> debit
     */
    private function createFileContentTinkoffBusiness(): string
    {
        $rows = [
            '0;1;Тип операции (пополнение/списание);3;4;5;6;Дата операции;8;9;10;Сумма платежа;Дочерняя категория',
            '0;1;Credit;3;4;5;6;01/01/2020;8;9;10;100.00;12;13;14;15;16;17;18;19;Переводы между счетами',
            '0;1;Debit;3;4;5;6;01/01/2020;8;9;10;100.00;12;13;14;15;16;17;18;19;Переводы между счетами',
            '0;1;Credit;3;4;5;6;01/01/2020;8;9;10;100.00;12;13;14;15;16;17;18;19;Доходы',
            '0;1;Debit;3;4;5;6;01/01/2020;8;9;10;100.00;12;13;14;15;16;17;18;19;Расходы',
        ];

        return implode("\n", $rows);
    }

    private function createFileContentTinkoffBusinessWithWrongTransactionType(): string
    {
        $rows = [
            '0;1;Тип операции (пополнение/списание);3;4;5;6;Дата операции;8;9;10;Сумма платежа;12;13;14;15;16;17;18;19;Дочерняя категория',
            '0;1;Wrong type;3;4;5;6;01/01/2020;8;9;10;100.00;12;13;14;15;16;17;18;19;Переводы между счетами',
        ];

        return implode("\n", $rows);
    }
}
