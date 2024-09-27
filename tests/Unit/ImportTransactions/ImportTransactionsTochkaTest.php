<?php

namespace Tests\Unit\ImportTransactions;

use App\Models\Account;
use App\Models\Integration;
use App\Models\Transaction;
use App\Services\ImportTransactions\ImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ImportTransactionsTochkaTest extends TestCase
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
            'integration_id' => Integration::findTochkaBank()->getKey(),
        ])->create();
        $file = UploadedFile::fake()->createWithContent(
            'transactions.csv',
            $this->createFileContentTochka()
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

    /**
     * Transactions rows
     * 1 -> transfer/credit
     * 2 -> transfer/debit
     * 3 -> credit
     * 4 -> debit
     */
    private function createFileContentTochka(): string
    {
        $rows = [
            ';;;;;;;;;;;;;;;;;;;;',
            '0;1;2;Дата операции;4;5;6;7;8;9;10;11;12;13;14;15;16;17;Списание;Зачисление;Назначение платежа',
            '0;1;2;01.01.2020 00:00:01;4;5;6;7;8;9;10;11;12;13;14;15;16;17;;100,00;Переводы между счетами',
            '0;1;2;01.01.2020 00:00:01;4;5;6;7;8;9;10;11;12;13;14;15;16;17;100,00;;Переводы между счетами',
            '0;1;2;01.01.2020 00:00:01;4;5;6;7;8;9;10;11;12;13;14;15;16;17;;100,00;Доходы',
            '0;1;2;01.01.2020 00:00:01;4;5;6;7;8;9;10;11;12;13;14;15;16;17;100,00;;Расходы',
        ];

        return implode("\n", $rows);
    }
}
