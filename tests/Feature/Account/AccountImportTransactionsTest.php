<?php

namespace Tests\Feature\Account;

use App\Jobs\TransactionsImportJob;
use App\Models\Account;
use App\Models\Integration;
use App\Models\TransactionsImport;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AccountImportTransactionsTest extends TestCase
{
    use DatabaseMigrations;

    private User $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = $this->userLogin();
    }

    public function testAccountImportTransactionsWhenNoOtherImports(): void
    {
        Queue::fake();

        Storage::fake('local');
        $file = UploadedFile::fake()->create('transactions.csv');

        $account = Account::factory()
            ->for(Integration::factory()->state([
                'code_id' => Integration::CODE_ID_TINKOFF_BANK,
            ]))
            ->create()
        ;

        $this->assertCount(0, TransactionsImport::all());
        $response = $this->postJson(route('api.v1.accounts.import', $account), [
            'account_id' => $account->getKey(),
            'file' => $file,
        ]);
        $this->assertCount(1, TransactionsImport::all());

        Queue::assertPushed(TransactionsImportJob::class);

        $response->assertOk();
        $response->assertExactJson([
            'message' => 'Ожидайдение завершения импорта',
        ]);

        $transactionsImport = TransactionsImport::query()->first();
        $this->assertSame($account->getKey(), $transactionsImport->account->getKey());
    }

    public function testAccountImportTransactionsWhenPreviousImportIsFinished(): void
    {
        Queue::fake();
        Storage::fake('local');

        collect(TransactionsImport::STATUSES)->each(function ($status, $statusId) {
            TransactionsImport::query()->truncate();

            // Processing doesn't allow imports
            if ($statusId === TransactionsImport::STATUS_ID_PROCESS) {
                return;
            }

            TransactionsImport::factory()->for($this->user)->create([
                'status_id' => $statusId,
            ]);

            $file = UploadedFile::fake()->create('transactions.csv');

            $account = Account::factory()
                ->for(Integration::factory()->state([
                    'code_id' => Integration::CODE_ID_TINKOFF_BANK,
                ]))
                ->create()
            ;

            $this->assertCount(1, TransactionsImport::all());
            $response = $this->postJson(route('api.v1.accounts.import', $account), [
                'account_id' => $account->getKey(),
                'file' => $file,
            ]);

            $this->assertCount(2, TransactionsImport::all());

            Queue::assertPushed(TransactionsImportJob::class);

            $response->assertOk();
            $response->assertExactJson([
                'message' => 'Ожидайдение завершения импорта',
            ]);
        });
    }

    public function testAccountCanNotImportTransactionsWithoutFile(): void
    {
        Queue::fake();

        $account = Account::factory()->create();

        $this->assertCount(0, TransactionsImport::all());
        $response = $this->postJson(route('api.v1.accounts.import', $account));
        $this->assertCount(0, TransactionsImport::all());

        Queue::assertNotPushed(TransactionsImportJob::class);

        $response->assertUnprocessable();
        $response->assertExactJson([
            'message' => 'Заполните форму правильно',
            'errors' => [
                'file' => ['Поле file обязательно.'],
            ],
        ]);
    }

    public function testAccountCanNotImportTransactionsWithWrongAccountId(): void
    {
        Queue::fake();

        Storage::fake('local');
        $file = UploadedFile::fake()->create('transactions.csv');

        $this->assertCount(0, TransactionsImport::all());
        $response = $this->postJson(route('api.v1.accounts.import', 2), [
            'file' => $file,
        ]);
        $this->assertCount(0, TransactionsImport::all());

        Queue::assertNotPushed(TransactionsImportJob::class);

        $response->assertUnprocessable();
        $response->assertExactJson([
            'message' => 'Заполните форму правильно',
            'errors' => [
                'account_id' => ['Аккаунт не найден.'],
            ],
        ]);
    }

    public function testAccountCanNotImportTransactionsIfImportIsRunningAlready(): void
    {
        Queue::fake();

        TransactionsImport::factory()->for($this->user)->create([
            'status_id' => TransactionsImport::STATUS_ID_PROCESS,
        ]);
        Storage::fake('local');
        $file = UploadedFile::fake()->create('transactions.csv');

        $account = Account::factory()
            ->for(Integration::factory()->state([
                'code_id' => Integration::CODE_ID_TINKOFF_BANK,
            ]))
            ->create()
        ;

        $this->assertCount(1, TransactionsImport::all());
        $response = $this->postJson(route('api.v1.accounts.import', $account), [
            'file' => $file,
        ]);
        $this->assertCount(1, TransactionsImport::all());

        Queue::assertNotPushed(TransactionsImportJob::class);

        $response->assertUnprocessable();
        $response->assertExactJson([
            'message' => 'Заполните форму правильно',
            'errors' => [
                'file' => ['Импорт уже запущен. Дождидесь завершения.'],
            ],
        ]);
    }
}
