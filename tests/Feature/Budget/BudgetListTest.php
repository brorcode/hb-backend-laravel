<?php

namespace Tests\Feature\Budget;

use App\Models\Budget;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class BudgetListTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->userLogin();
    }

    public function testBudgetListApiReturnsCorrectResponse(): void
    {
        $budgets = Budget::factory(11)
            ->sequence(
                ['period_on' => now()->subMonths(12)->startOfMonth()],
                ['period_on' => now()->subMonths(11)->startOfMonth()],
                ['period_on' => now()->subMonths(10)->startOfMonth()],
                ['period_on' => now()->subMonths(9)->startOfMonth()],
                ['period_on' => now()->subMonths(8)->startOfMonth()],
                ['period_on' => now()->subMonths(7)->startOfMonth()],
                ['period_on' => now()->subMonths(6)->startOfMonth()],
                ['period_on' => now()->subMonths(5)->startOfMonth()],
                ['period_on' => now()->subMonths(4)->startOfMonth()],
                ['period_on' => now()->subMonths(3)->startOfMonth()],
                ['period_on' => now()->subMonths(2)->startOfMonth()],
            )
            ->create()
        ;

        $data = $budgets->take(10)->map(function (Budget $budget) {
            return [
                'id' => $budget->period_on->format('Ym'),
                'total' => $budget->amount / 100,
                'period_on_for_list' => $budget->period_on->translatedFormat('Y F'),
                'period_on' => $budget->period_on->toDateString(),
                'deletable' => $budget->period_on->gt(now()),
            ];
        });

        $response = $this->postJson(route('api.v1.budgets.index'), [
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
    public function testBudgetListApiReturnsValidationErrors(array $request): void
    {
        $response = $this->postJson(route('api.v1.budgets.index'), $request);
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
