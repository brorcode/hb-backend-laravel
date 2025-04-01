<?php

namespace Tests\Feature\BudgetTemplate;

use App\Models\BudgetTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class BudgetTemplateListTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->userLogin();
    }

    public function testBudgetTemplateListApiReturnsCorrectResponse(): void
    {
        $budgetTemplates = BudgetTemplate::factory(11)->create();
        $data = $budgetTemplates->take(10)->map(function (BudgetTemplate $budgetTemplate) {
            return [
                'id' => $budgetTemplate->getKey(),
                'amount' => $budgetTemplate->amount / 100,
                'category' => $budgetTemplate->category->only(['id', 'name']),
            ];
        });

        $response = $this->postJson(route('api.v1.budget-templates.index'), [
            'page' => 1,
            'limit' => 10,
        ]);

        $response->assertOk();
        $response->assertJsonCount(10, 'data');
        $response->assertExactJson([
            'data' => $data->toArray(),
            'sum' => $budgetTemplates->sum('amount') / 100,
            'meta' => [
                'currentPage' => 1,
                'hasNextPage' => true,
                'perPage' => 10,
            ],
        ]);
    }

    #[DataProvider('invalidDataProvider')]
    public function testBudgetTemplateListApiReturnsValidationErrors(array $request): void
    {
        $response = $this->postJson(route('api.v1.budget-templates.index'), $request);
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
