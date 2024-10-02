<?php

namespace Tests\Feature\Transaction;

use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionDestroyTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->userLogin();
    }

    public function testTransactionDestroy(): void
    {
        $transactions = Transaction::factory(2)->create();

        /** @var Transaction $transactionToBeDeleted */
        $transactionToBeDeleted = $transactions->last();

        $this->assertCount(2, Transaction::all());
        $this->assertDatabaseHas((new Transaction())->getTable(), [
            'id' => $transactionToBeDeleted->getKey(),
        ]);
        $response = $this->deleteJson(route('api.v1.transactions.destroy', $transactionToBeDeleted));

        $this->assertCount(1, Transaction::all());
        $this->assertDatabaseMissing((new Transaction())->getTable(), [
            'id' => $transactionToBeDeleted->getKey(),
        ]);

        $response->assertOk();
        $response->assertExactJson([
            'message' => 'Транзакция удалена',
        ]);
    }

    public function testTransactionDestroyMany(): void
    {
        $transactions = Transaction::factory(2)->create();

        $this->assertCount(2, Transaction::all());
        $response = $this->deleteJson(route('api.v1.transactions.destroy-many'), [
            'selected_items' => $transactions->pluck('id')->toArray(),
        ]);

        $this->assertCount(0, Transaction::all());

        $response->assertOk();
        $response->assertExactJson([
            'message' => 'Транзакции удалены',
        ]);
    }

    public function testTransactionDestroyManyValidation(): void
    {
        $response = $this->deleteJson(route('api.v1.transactions.destroy-many'));

        $response->assertUnprocessable();
        $response->assertExactJson([
            'message' => 'Заполните форму правильно',
            'errors' => [
                'selected_items' => ['Поле selected items обязательно.'],
            ],
        ]);
    }
}
