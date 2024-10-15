<?php

namespace Tests\Unit\Jobs;

use App\Exceptions\SystemException;
use App\Jobs\TransactionsImportJob;
use App\Models\Account;
use App\Models\Integration;
use App\Models\Transaction;
use App\Models\TransactionsImport;
use App\Models\User;
use App\Services\ImportTransactions\ImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Mockery\MockInterface;
use Tests\TestCase;

class TransactionsImportJobTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private TransactionsImport $transactionsImport;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = $this->userLogin();

        Storage::fake('local');
        $filePath = 'transactions.csv';
        Storage::put($filePath, $this->getFileContent());

        Integration::factory()->create(['code_id' => Integration::CODE_ID_TINKOFF_BANK]);
        $account = Account::factory([
            'integration_id' => Integration::findTinkoffBank()->getKey(),
        ])->create();

        $this->transactionsImport = TransactionsImport::factory()
            ->for($this->user)
            ->for($account)
            ->create([
                'file_path' => $filePath,
                'imported_count' => 0,
                'status_id' => TransactionsImport::STATUS_ID_PROCESS,
                'finished_at' => null,
                'error' => null,
            ])
        ;
    }

    private function dispatch(): void
    {
        (new TransactionsImportJob($this->user, $this->transactionsImport, $this->transactionsImport->account))->handle();
    }

    public function testTransactionsImportJobCanImportTransactionsSuccessfully(): void
    {
        $this->assertCount(0, Transaction::all());
        $this->assertCount(1, TransactionsImport::all());
        $this->dispatch();
        $this->assertCount(2, Transaction::all());
        $this->assertCount(1, TransactionsImport::all());

        $this->assertDatabaseHas((new TransactionsImport())->getTable(), [
            'imported_count' => 2,
            'status_id' => TransactionsImport::STATUS_ID_SUCCESS,
            'error' => null,
        ]);
    }

    public function testTransactionsImportJobChangesStatusToFailedWhenImportError(): void
    {
        $this->mock(ImportService::class, function (MockInterface $mock) {
            $mock->shouldReceive('handle')
                ->once()
                ->andThrow(new SystemException('An error occurred'))
            ;
        });

        $this->assertCount(0, Transaction::all());
        $this->assertCount(1, TransactionsImport::all());
        $this->dispatch();
        $this->assertCount(0, Transaction::all());
        $this->assertCount(1, TransactionsImport::all());

        $this->assertDatabaseHas((new TransactionsImport())->getTable(), [
            'imported_count' => 0,
            'status_id' => TransactionsImport::STATUS_ID_FAILED,
            'error' => 'An error occurred',
        ]);
    }

    public function testTransactionsImportJobChangesStatusToFailedWhenJobFailsForAnyReason(): void
    {
        $exception = new SystemException('An error occurred');
        (new TransactionsImportJob($this->user, $this->transactionsImport, $this->transactionsImport->account))->failed($exception);

        $this->assertDatabaseHas((new TransactionsImport())->getTable(), [
            'imported_count' => 0,
            'status_id' => TransactionsImport::STATUS_ID_FAILED,
            'error' => 'An error occurred',
        ]);
    }

    public function testTransactionsImportJobReturnsCorrectTagNames(): void
    {
        $tagNames = (new TransactionsImportJob($this->user, $this->transactionsImport, $this->transactionsImport->account))->tags();
        $this->assertEquals(['TransactionsImportJob'], $tagNames);
    }

    private function getFileContent(): string
    {
        $rows = [
            'Дата операции;1;2;Статус;4;5;Сумма платежа;7;8;Категория;10;Описание;12',
            '01.01.2024 00:00:03;1;2;OK;4;5;100,00;6;8;Доходы;10;Доход;12',
            '01.01.2024 00:00:04;1;2;OK;4;5;-100,00;6;8;Расходы;10;Расход;12',
        ];

        return implode("\n", $rows);
    }
}
