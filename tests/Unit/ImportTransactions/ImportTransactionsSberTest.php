<?php

namespace Tests\Unit\ImportTransactions;

use App\Exceptions\SystemException;
use App\Models\Account;
use App\Models\Integration;
use App\Models\Transaction;
use App\Services\ImportTransactions\ImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImportTransactionsSberTest extends TestCase
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
            'integration_id' => Integration::findSberBank()->getKey(),
        ])->create();

        Storage::fake('local');
        $filePath = 'transactions.csv';
        Storage::put($filePath, $this->createFileContentSber());

        $this->assertSame(0, Transaction::query()->count());

        $this->expectException(SystemException::class);
        $this->expectExceptionMessage('Автоимпорт для Сбербанка отключен, внесите транзакции вручную.');
        $service = ImportService::create();
        $service->handle($filePath, $account);

        $this->assertSame(0, Transaction::query()->count());

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
    private function createFileContentSber(): string
    {
        $rows = [
            '0;1;Дата совершения операции;3;4;5;6;7;Описание;9;10;Сумма в валюте счета;',
            '0;1;01.01.2020;3;4;5;6;7;Переводы между счетами;9;10;100;',
            '0;1;01.01.2020;3;4;5;6;7;Переводы между счетами;9;10;-100;',
            '0;1;01.01.2020;3;4;5;6;7;Доходы;9;10;100;',
            '0;1;01.01.2020;3;4;5;6;7;Расходы;9;10;-100;',
        ];

        return implode("\n", $rows);
    }
}
