<?php

namespace Tests\Feature\Loan;

use App\Models\Loan;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class LoanListTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->userLogin();
    }

    public function testLoanListApiReturnsCorrectResponse(): void
    {
        $loans = Loan::factory()
            ->count(11)
            ->state([
                'amount' => 30,
                'type_id' => Loan::TYPE_ID_CREDIT,
            ])
            ->has(Transaction::factory()->count(3)->state([
                'amount' => 10,
                'is_debit' => false,
                'is_transfer' => false,
            ]))->create()
        ;

        $data = $loans->take(10)->map(function (Loan $loan) {
            return [
                'id' => $loan->getKey(),
                'name' => $loan->name,
                'type' => [
                    'id' => $loan->type_id,
                    'name' => Loan::TYPES[$loan->type_id],
                ],
                'amount' => 30,
                'amount_left' => 30 / 100,
                'deadline_on' => $loan->deadline_on->toDateString(),
                'created_at' => $loan->created_at,
                'updated_at' => $loan->updated_at,
            ];
        });

        $response = $this->postJson(route('api.v1.loans.index'), [
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
    public function testLoanListApiReturnsValidationErrors(array $request): void
    {
        $response = $this->postJson(route('api.v1.loans.index'), $request);
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
