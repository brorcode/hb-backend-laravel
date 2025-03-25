<?php

namespace Tests\Feature\BudgetItem;

use App\Models\Budget;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class BudgetItemListTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->userLogin();
    }

    public function testBudgetItemListApiReturnsCorrectResponse(): void
    {
        $budgetItems = Budget::factory(11)->create(
            ['period_on' => '2025-02-01']
        );
        $data = $budgetItems->take(10)->map(function (Budget $budgetItem) {
            return [
                'id' => $budgetItem->getKey(),
                'amount' => $budgetItem->amount / 100,
                'category' => $budgetItem->category->only(['id', 'name']),
                'period_on_for_list' => '2025 февраль',
            ];
        });

        $response = $this->postJson(route('api.v1.budget-items.index'), [
            'page' => 1,
            'limit' => 10,
        ]);

        $response->assertOk();
        $response->assertJsonCount(10, 'data');
        $response->assertExactJson([
            'data' => $data->toArray(),
            'sum' => $budgetItems->sum('amount') / 100,
            'meta' => [
                'currentPage' => 1,
                'hasNextPage' => true,
                'perPage' => 10,
            ],
        ]);
    }

    #[DataProvider('invalidDataProvider')]
    public function testBudgetItemListApiReturnsValidationErrors(array $request): void
    {
        $response = $this->postJson(route('api.v1.budget-items.index'), $request);
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
