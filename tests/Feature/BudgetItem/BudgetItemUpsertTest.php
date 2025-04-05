<?php

namespace Tests\Feature\BudgetItem;

use App\Models\Budget;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class BudgetItemUpsertTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->userLogin();
    }

    public function testBudgetItemShow(): void
    {
        /** @var Budget $budgetItem */
        $budgetItem = Budget::factory()
            ->create([
                'amount' => 123,
            ])
        ;
        $response = $this->getJson(route('api.v1.budget-items.show', $budgetItem));

        $response->assertOk();
        $response->assertExactJson([
            'data' => [
                'id' => $budgetItem->getKey(),
                'amount' => 1.23,
                'category' => $budgetItem->category->only(['id', 'name']),
                'period_on_for_list' => $budgetItem->period_on->translatedFormat('Y F'),
            ],
        ]);
    }

    public function testBudgetItemStore(): void
    {
        $this->assertCount(0, Budget::all());
        $category = Category::factory()->withParentCategory()->create();

        $response = $this->postJson(route('api.v1.budget-items.store'), [
            'amount' => 1000,
            'category_id' => $category->getKey(),
            'period_on' => 202502,
        ]);

        $this->assertCount(1, Budget::all());
        $this->assertDatabaseHas((new Budget())->getTable(), [
            'amount' => 100000,
            'category_id' => $category->getKey(),
            'period_on' => '2025-02-01',
        ]);

        $response->assertCreated();
        $response->assertExactJson([
            'message' => 'Элемент бюджета создан',
        ]);
    }

    public function testBudgetItemCannotStoreTwoSameCategories(): void
    {
        /** @var Budget $budgetItem */
        $budgetItem = Budget::factory()->create();
        $this->assertCount(1, Budget::all());

        $response = $this->postJson(route('api.v1.budget-items.store'), [
            'amount' => 1000,
            'category_id' => $budgetItem->category_id,
            'period_on' => $budgetItem->period_on->format('Ym'),
        ]);

        $this->assertCount(1, Budget::all());

        $response->assertUnprocessable();
        $response->assertExactJson([
            'message' => 'Заполните форму правильно',
            'errors' => [
                'category_id' => ['Такое значение уже существует.'],
            ],
        ]);
    }

    #[DataProvider('invalidBudgetItemDataProvider')]
    public function testBudgetItemCanNotBeStoredWithInvalidData(array $request, array $errors): void
    {
        $response = $this->postJson(route('api.v1.budget-items.store'), $request);

        $response->assertUnprocessable();
        $response->assertExactJson([
            'message' => 'Заполните форму правильно',
            'errors' => $errors,
        ]);
    }

    public function testBudgetItemUpdate(): void
    {
        /** @var Budget $budgetItem */
        $budgetItem = Budget::factory()->create();

        $category = Category::factory()->create();

        $this->assertCount(1, Budget::all());
        $this->assertDatabaseMissing((new Budget())->getTable(), [
            'amount' => 10012,
            'category_id' => $category->getKey(),
            'period_on' => $budgetItem->period_on->toDateString(),
        ]);

        $response = $this->putJson(route('api.v1.budget-items.update', $budgetItem), [
            'amount' => 100.12,
            'category_id' => $category->getKey(),
            'period_on' => $budgetItem->period_on->format('Ym'),
        ]);

        $response->assertOk();

        $this->assertCount(1, Budget::all());
        $this->assertDatabaseHas((new Budget())->getTable(), [
            'amount' => 10012,
            'category_id' => $category->getKey(),
            'period_on' => $budgetItem->period_on->toDateString(),
        ]);

        $response->assertExactJson([
            'message' => 'Элемент бюджета обновлен',
            'data' => [
                'id' => $budgetItem->getKey(),
                'amount' => 100.12,
                'category' => $category->only(['id', 'name']),
                'period_on_for_list' => $budgetItem->period_on->translatedFormat('Y F'),
            ],
        ]);
    }

    public function testCanUpdateBudgetItemAmountForSameCategory(): void
    {
        /** @var Budget $budgetItem */
        $budgetItem = Budget::factory()->create();

        $this->assertCount(1, Budget::all());
        $this->assertDatabaseMissing((new Budget())->getTable(), [
            'amount' => 100,
            'category_id' => $budgetItem->category_id,
            'period_on' => $budgetItem->period_on->toDateString(),
        ]);

        $response = $this->putJson(route('api.v1.budget-items.update', $budgetItem), [
            'amount' => 1.00,
            'category_id' => $budgetItem->category_id,
            'period_on' => $budgetItem->period_on->format('Ym'),
        ]);

        $response->assertOk();

        $this->assertCount(1, Budget::all());
        $this->assertDatabaseHas((new Budget())->getTable(), [
            'amount' => 100,
            'category_id' => $budgetItem->category_id,
            'period_on' => $budgetItem->period_on->toDateString(),
        ]);

        $response->assertExactJson([
            'message' => 'Элемент бюджета обновлен',
            'data' => [
                'id' => $budgetItem->getKey(),
                'amount' => 1.00,
                'category' => $budgetItem->category->only(['id', 'name']),
                'period_on_for_list' => $budgetItem->period_on->translatedFormat('Y F'),
            ],
        ]);
    }

    #[DataProvider('invalidBudgetItemDataProvider')]
    public function testBudgetItemCanNotBeUpdatedWithInvalidData(array $request, array $errors): void
    {
        $budgetItem = Budget::factory()->create();

        $response = $this->putJson(route('api.v1.budget-items.update', $budgetItem), $request);

        $response->assertUnprocessable();
        $response->assertExactJson([
            'message' => 'Заполните форму правильно',
            'errors' => $errors,
        ]);
    }

    public static function invalidBudgetItemDataProvider(): array
    {
        return [
            'wrong_data_1' => [
                'request' => [],
                'errors' => [
                    'amount' => ['Поле amount обязательно.'],
                    'category_id' => ['Поле category id обязательно.'],
                    'period_on' => ['Поле period on обязательно.'],
                ],
            ],
            'wrong_data_2' => [
                'request' => [
                    'amount' => 'test',
                    'category_id' => 99,
                    'period_on' => false,
                ],
                'errors' => [
                    'amount' => ['Значение поля amount должно быть числом.'],
                    'category_id' => ['Такого значения не существует.'],
                    'period_on' => ['Значение поля period on должно быть целым числом.'],
                ],
            ],
        ];
    }
}
