<?php

namespace Tests\Feature\Transaction;

use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class TransactionListTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->userLogin();
    }

    public function testTransactionListApiReturnsCorrectResponse(): void
    {
        $transactions = Transaction::factory(11)->create();
        $data = $transactions->take(10)->map(function (Transaction $transaction) {
            return [
                'id' => $transaction->getKey(),
                'amount' => $transaction->amount / 100,
                'category' => $transaction->category->only(['id', 'name']),
                'account' => $transaction->account->only(['id', 'name']),
                'loan' => null,
                'tags' => [],
                'is_debit' => $transaction->is_debit,
                'is_transfer' => $transaction->is_transfer,
                'created_at' => $transaction->created_at,
                'updated_at' => $transaction->updated_at,
            ];
        });

        $response = $this->postJson(route('api.v1.transactions.index'), [
            'page' => 1,
            'limit' => 10,
        ]);

        $response->assertOk();
        $response->assertJsonCount(10, 'data');
        $response->assertExactJson([
            'data' => $data->toArray(),
            'sum' => $transactions->sum('amount') / 100,
            'meta' => [
                'currentPage' => 1,
                'hasNextPage' => true,
                'perPage' => 10,
            ],
        ]);
    }

    #[DataProvider('invalidDataProvider')]
    public function testTransactionListApiReturnsValidationErrors(array $request): void
    {
        $response = $this->postJson(route('api.v1.transactions.index'), $request);
        $response->assertBadRequest();
        $response->assertExactJson([
            'message' => 'Ошибка сервера. Попробуйте еще раз',
        ]);
    }

    public static function invalidDataProvider(): array
    {
        return [
            'wrong_data_1' => [
                'request' => [
                    'sorting' => 10,
                    'filters' => 10,
                ],
            ],
            'wrong_data_2' => [
                'request' => [
                    'page' => 1,
                    'limit' => 10,
                    'sorting' => [
                        'column' => true,
                        'direction' => 'direction',
                    ],
                ],
            ],
        ];
    }
}
