<?php

namespace Tests\Feature\Account;

use App\Models\TransactionsImport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountCheckImportStatusTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->user =$this->userLogin();
    }

    public function testAccountImportStatusReturnsCorrectResponseWhenNoImportsInProgress(): void
    {
        $response = $this->getJson(route('api.v1.accounts.check.import-status'));

        $response->assertOk();
        $response->assertExactJson([
            'data' => [
                'is_finished' => true,
            ],
        ]);
    }

    public function testAccountImportStatusReturnsCorrectResponseWhenImportInProgress(): void
    {
        TransactionsImport::factory()->for($this->user)->create(['status_id' => TransactionsImport::STATUS_ID_PROCESS]);

        $this->assertCount(1, TransactionsImport::all());
        $response = $this->getJson(route('api.v1.accounts.check.import-status'));

        $response->assertOk();
        $response->assertExactJson([
            'data' => [
                'is_finished' => false,
            ],
        ]);
        $this->assertCount(1, TransactionsImport::all());
    }

    public function testAccountImportStatusReturnsCorrectResponseWhenImportIsFinishedSuccessfully(): void
    {
        TransactionsImport::factory()->for($this->user)->create(['status_id' => TransactionsImport::STATUS_ID_SUCCESS]);

        $this->assertCount(1, TransactionsImport::all());
        $response = $this->getJson(route('api.v1.accounts.check.import-status'));

        $response->assertOk();
        $response->assertExactJson([
            'data' => [
                'is_finished' => true,
            ],
            'message' => 'Импорт транзакций завершен',
        ]);
        $this->assertCount(0, TransactionsImport::all());
    }

    public function testAccountImportStatusReturnsCorrectResponseWhenImportIsFailed(): void
    {
        TransactionsImport::factory()->for($this->user)->create(['status_id' => TransactionsImport::STATUS_ID_FAILED]);

        $this->assertCount(1, TransactionsImport::all());
        $response = $this->getJson(route('api.v1.accounts.check.import-status'));

        $response->assertOk();
        $response->assertExactJson([
            'data' => [
                'is_finished' => true,
            ],
            'message' => 'Импорт транзакций завершен с ошибками',
        ]);
        $this->assertCount(0, TransactionsImport::all());
    }
}
