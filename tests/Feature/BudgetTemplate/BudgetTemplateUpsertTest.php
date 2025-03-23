<?php

namespace Tests\Feature\BudgetTemplate;

use App\Models\BudgetTemplate;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class BudgetTemplateUpsertTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->userLogin();
    }

    public function testBudgetTemplateShow(): void
    {
        /** @var BudgetTemplate $budgetTemplate */
        $budgetTemplate = BudgetTemplate::factory()
            ->create([
                'amount' => 123,
            ])
        ;
        $response = $this->getJson(route('api.v1.budget-templates.show', $budgetTemplate));

        $response->assertOk();
        $response->assertExactJson([
            'data' => [
                'id' => $budgetTemplate->getKey(),
                'amount' => 1.23,
                'category' => $budgetTemplate->category->only(['id', 'name']),
                'created_at' => $budgetTemplate->created_at,
                'updated_at' => $budgetTemplate->updated_at,
            ],
        ]);
    }

    public function testBudgetTemplateStore(): void
    {
        $this->assertCount(0, BudgetTemplate::all());
        $category = Category::factory()->withParentCategory()->create();

        $response = $this->postJson(route('api.v1.budget-templates.store'), [
            'amount' => 1000,
            'category_id' => $category->getKey(),
        ]);

        $this->assertCount(1, BudgetTemplate::all());
        $this->assertDatabaseHas((new BudgetTemplate())->getTable(), [
            'amount' => 100000,
            'category_id' => $category->getKey(),
        ]);

        $response->assertCreated();
        $response->assertExactJson([
            'message' => 'Шаблон бюджета создан',
        ]);
    }

    public function testBudgetTemplateCannotStoreTwoSameCategories(): void
    {
        /** @var BudgetTemplate $budgetTemplate */
        $budgetTemplate = BudgetTemplate::factory()->create();
        $this->assertCount(1, BudgetTemplate::all());

        $response = $this->postJson(route('api.v1.budget-templates.store'), [
            'amount' => 1000,
            'category_id' => $budgetTemplate->category_id,
        ]);

        $this->assertCount(1, BudgetTemplate::all());

        $response->assertUnprocessable();
        $response->assertExactJson([
            'message' => 'Заполните форму правильно',
            'errors' => [
                'category_id' => ['Такое значение уже существует.'],
            ],
        ]);
    }

    #[DataProvider('invalidBudgetTemplateDataProvider')]
    public function testBudgetTemplateCanNotBeStoredWithInvalidData(array $request, array $errors): void
    {
        $response = $this->postJson(route('api.v1.budget-templates.store'), $request);

        $response->assertUnprocessable();
        $response->assertExactJson([
            'message' => 'Заполните форму правильно',
            'errors' => $errors,
        ]);
    }

    public function testBudgetTemplateUpdate(): void
    {
        /** @var BudgetTemplate $budgetTemplate */
        $budgetTemplate = BudgetTemplate::factory()->create();

        $category = Category::factory()->withParentCategory()->create();

        $this->assertCount(1, BudgetTemplate::all());
        $this->assertDatabaseMissing((new BudgetTemplate())->getTable(), [
            'amount' => 10012,
            'category_id' => $category->getKey(),
        ]);

        $response = $this->putJson(route('api.v1.budget-templates.update', $budgetTemplate), [
            'amount' => 100.12,
            'category_id' => $category->getKey(),
        ]);

        $this->assertCount(1, BudgetTemplate::all());
        $this->assertDatabaseHas((new BudgetTemplate())->getTable(), [
            'amount' => 10012,
            'category_id' => $category->getKey(),
        ]);

        $response->assertOk();

        $freshBudgetTemplate = $budgetTemplate->fresh();
        $response->assertExactJson([
            'message' => 'Шаблон бюджета обновлен',
            'data' => [
                'id' => $budgetTemplate->getKey(),
                'amount' => 100.12,
                'category' => $category->only(['id', 'name']),
                'created_at' => $freshBudgetTemplate->created_at,
                'updated_at' => $freshBudgetTemplate->updated_at,
            ],
        ]);
    }

    #[DataProvider('invalidBudgetTemplateDataProvider')]
    public function testBudgetTemplateCanNotBeUpdatedWithInvalidData(array $request, array $errors): void
    {
        $budgetTemplate = BudgetTemplate::factory()->create();

        $response = $this->putJson(route('api.v1.budget-templates.update', $budgetTemplate), $request);

        $response->assertUnprocessable();
        $response->assertExactJson([
            'message' => 'Заполните форму правильно',
            'errors' => $errors,
        ]);
    }

    public static function invalidBudgetTemplateDataProvider(): array
    {
        return [
            'wrong_data_1' => [
                'request' => [],
                'errors' => [
                    'amount' => ['Поле amount обязательно.'],
                    'category_id' => ['Поле category id обязательно.'],
                ],
            ],
            'wrong_data_2' => [
                'request' => [
                    'amount' => 'test',
                    'category_id' => 99,
                ],
                'errors' => [
                    'amount' => ['Значение поля amount должно быть числом.'],
                    'category_id' => ['Такого значения не существует.'],
                ],
            ],
        ];
    }
}
