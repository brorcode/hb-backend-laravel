<?php

namespace Tests\Feature\Account;

use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AccountListTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->userLogin();
    }

    public function testAccountListApiReturnsCorrectResponse(): void
    {
        $accounts = Account::factory()
            ->count(11)
            ->has(Transaction::factory()->count(3)->state([
                'amount' => 1000,
                'is_debit' => true,
                'is_transfer' => false,
            ]))
            ->create()
        ;

        $data = $accounts->take(10)->map(function (Account $account) {
            return [
                'id' => $account->getKey(),
                'name' => $account->name,
                'amount' => 3*10,
                'created_at' => $account->created_at,
                'updated_at' => $account->updated_at,
            ];
        });

        $response = $this->postJson(route('api.v1.accounts.index'), [
            'page' => 1,
            'limit' => 10,
        ]);

        $response->assertOk();
        $response->assertJsonCount(10, 'data');
        $response->assertExactJson([
            'data' => $data->toArray(),
            'meta' => [
                'currentPage' => 1,
                'hasNextPage' => true,
                'perPage' => 10,
            ],
        ]);
    }

    #[DataProvider('invalidDataProvider')]
    public function testAccountListApiReturnsValidationErrors(array $request): void
    {
        $response = $this->postJson(route('api.v1.accounts.index'), $request);
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
