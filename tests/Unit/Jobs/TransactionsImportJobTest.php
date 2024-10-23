<?php

namespace Tests\Unit\Jobs;

use App\Exceptions\SystemException;
use App\Jobs\TransactionsImportJob;
use App\Models\Account;
use App\Models\Integration;
use App\Models\Notification;
use App\Models\Transaction;
use App\Models\TransactionsImport;
use App\Models\User;
use App\Services\ImportTransactions\ImportService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Mockery\MockInterface;
use Tests\TestCase;

class TransactionsImportJobTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Account $account;
    private TransactionsImport $transactionsImport;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = $this->userLogin();

        Storage::fake('local');
        $filePath = 'transactions.csv';
        Storage::put($filePath, $this->getFileContent());

        Integration::factory()->create(['code_id' => Integration::CODE_ID_TINKOFF_BANK]);
        $this->account = Account::factory([
            'integration_id' => Integration::findTinkoffBank()->getKey(),
        ])->create();

        $this->transactionsImport = TransactionsImport::factory()
            ->for($this->user)
            ->for($this->account)
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
        $this->assertCount(0, Notification::all());
        $this->assertCount(0, Transaction::all());
        $this->assertCount(1, TransactionsImport::all());
        $this->dispatch();
        $this->assertCount(210, Transaction::all());
        $this->assertCount(1, TransactionsImport::all());

        $this->assertDatabaseHas((new TransactionsImport())->getTable(), [
            'imported_count' => 210,
            'status_id' => TransactionsImport::STATUS_ID_SUCCESS,
            'error' => null,
        ]);

        $this->assertCount(4, Notification::all());
        $this->assertDatabaseHas((new Notification())->getTable(), [
            'message' => 'Общее количество транзакций для импорта 210',
        ]);
        $this->assertDatabaseHas((new Notification())->getTable(), [
            'message' => 'Идет импорт... Импортировано 100 транзакции из 210',
        ]);
        $this->assertDatabaseHas((new Notification())->getTable(), [
            'message' => 'Идет импорт... Импортировано 200 транзакции из 210',
        ]);
        $this->assertDatabaseHas((new Notification())->getTable(), [
            'message' => "Импорт транзакций для {$this->account->name} завершен. Импортировано 210 транзакции из 210",
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
        $this->assertCount(0, Notification::all());
        (new TransactionsImportJob($this->user, $this->transactionsImport, $this->transactionsImport->account))->failed($exception);

        $this->assertDatabaseHas((new TransactionsImport())->getTable(), [
            'imported_count' => 0,
            'status_id' => TransactionsImport::STATUS_ID_FAILED,
            'error' => 'An error occurred',
        ]);
        $this->assertCount(1, Notification::all());
        $this->assertDatabaseHas((new Notification())->getTable(), [
            'message' => "Импорт транзакций для {$this->account->name} завершен с ошибками",
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
        ];
        $date = Carbon::createFromDate(2024, 1, 1);

        for ($i = 1; $i <= 210; $i++) {
            $dateString = $date->addMinute()->format('d.m.Y H:i:s');
            $status = 'OK';
            $amount = ($i % 2 === 0) ? '100,00' : '-50,00';
            $category = ($i % 2 === 0) ? 'Доходы' : 'Расходы';

            $newRow = "$dateString;1;2;$status;4;5;$amount;6;8;$category;10;$category Description;12";

            $rows[] = $newRow;
        }

        return implode("\n", $rows);
    }
}
