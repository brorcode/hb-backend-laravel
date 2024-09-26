<?php

namespace Tests\Feature\Account;

use App\Models\Account;
use App\Models\Integration;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AccountImportTransactionsTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp(): void
    {
        parent::setUp();

        $this->userLogin();
    }

    public function testAccountImportTransactions(): void
    {
        Storage::fake('local');
        $file = UploadedFile::fake()->create('transactions.csv');

        $account = Account::factory()
            ->for(Integration::factory()->state([
                'code_id' => Integration::CODE_ID_TINKOFF_BANK,
            ]))
            ->create()
        ;

        $response = $this->postJson(route('api.v1.accounts.import', $account), [
            'account_id' => $account->getKey(),
            'file' => $file,
        ]);

        $response->assertOk();
        $response->assertExactJson([
            'message' => 'Добавлено новых транзакций: 0',
        ]);
    }

    public function testAccountCanNotImportTransactionsWithoutFile(): void
    {
        $account = Account::factory()->create();

        $response = $this->postJson(route('api.v1.accounts.import', $account));

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
        Storage::fake('local');
        $file = UploadedFile::fake()->create('transactions.csv');

        $response = $this->postJson(route('api.v1.accounts.import', 2), [
            'file' => $file,
        ]);

        $response->assertUnprocessable();
        $response->assertExactJson([
            'message' => 'Заполните форму правильно',
            'errors' => [
                'account_id' => ['Аккаунт не найден.'],
            ],
        ]);
    }
}
