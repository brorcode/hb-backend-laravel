<?php

namespace Tests\Feature\Category;

use App\Models\Category;
use App\Models\Scopes\OwnerScope;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CategoryUpsertTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->userLogin();
    }

    public function testCategoryShow(): void
    {
        /** @var Category $category */
        $category = Category::factory()->create();
        $response = $this->getJson(route('api.v1.categories.show', $category));

        $response->assertOk();
        $response->assertExactJson([
            'data' => [
                'id' => $category->getKey(),
                'name' => $category->name,
                'parent_category' => null,
                'transactions_credit' => [
                    'amount' => 0,
                    'count' => 0,
                ],
                'transactions_debit' => [
                    'amount' => 0,
                    'count' => 0,
                ],
                'transactions_transfer' => [
                    'amount' => 0,
                    'count' => 0,
                ],
                'created_at' => $category->created_at,
                'updated_at' => $category->updated_at,
            ],
        ]);
    }

    public function testCategoryStore(): void
    {
        $parentCategory = Category::factory()->create();
        $this->assertCount(1, Category::all());

        $response = $this->postJson(route('api.v1.categories.store'), [
            'name' => 'test',
            'parent_id' => $parentCategory->getKey(),
        ]);

        $this->assertCount(2, Category::all());
        $this->assertDatabaseHas((new Category())->getTable(), [
            'name' => 'test',
            'parent_id' => $parentCategory->getKey(),
        ]);

        $response->assertCreated();
        $response->assertExactJson([
            'message' => 'Категория создана',
        ]);
    }

    #[DataProvider('invalidCategoryDataProvider')]
    public function testCategoryCanNotBeStoredWithInvalidData(array $request, array $errors): void
    {
        Category::factory()->create(['name' => 'existing category name']);

        $response = $this->postJson(route('api.v1.categories.store'), $request);

        $response->assertUnprocessable();
        $response->assertExactJson([
            'message' => 'Заполните форму правильно',
            'errors' => $errors,
        ]);
    }

    public function testCategoryUpdate(): void
    {
        /** @var Category $parentCategory */
        $parentCategory = Category::factory()->create();

        /** @var Category $category */
        $category = Category::factory()->withParentCategory()->create(['name' => 'test category name']);
        $this->assertCount(3, Category::all());
        $this->assertDatabaseMissing((new Category())->getTable(), [
            'name' => 'new category name',
            'parent_id' => $parentCategory->getKey(),
        ]);

        $response = $this->putJson(route('api.v1.categories.update', $category), [
            'name' => 'new category name',
            'parent_id' => $parentCategory->getKey(),
        ]);

        $this->assertCount(3, Category::all());
        $this->assertDatabaseHas((new Category())->getTable(), [
            'name' => 'new category name',
            'parent_id' => $parentCategory->getKey(),
        ]);

        $response->assertOk();

        $freshCategory = $category->fresh();
        $response->assertExactJson([
            'message' => 'Категория обновлена',
            'data' => [
                'id' => $freshCategory->getKey(),
                'name' => 'new category name',
                'parent_category' => [
                    'id' => $parentCategory->getKey(),
                    'name' => $parentCategory->name,
                ],
                'transactions_credit' => [
                    'amount' => 0,
                    'count' => 0,
                ],
                'transactions_debit' => [
                    'amount' => 0,
                    'count' => 0,
                ],
                'transactions_transfer' => [
                    'amount' => 0,
                    'count' => 0,
                ],
                'created_at' => $freshCategory->created_at,
                'updated_at' => $freshCategory->updated_at,
            ],
        ]);
    }

    #[DataProvider('invalidCategoryDataProvider')]
    public function testUserCanNotBeUpdatedWithInvalidData(array $request, array $errors): void
    {
        Category::factory()->create(['name' => 'existing category name']);
        $categoryForUpdate = Category::factory()->create(['name' => 'test category name']);

        $response = $this->putJson(route('api.v1.categories.update', $categoryForUpdate), $request);

        $response->assertUnprocessable();
        $response->assertExactJson([
            'message' => 'Заполните форму правильно',
            'errors' => $errors,
        ]);
    }

    public function testCategoryCanBeUpdatedWithOutNameChange(): void
    {
        /** @var Category $category */
        $category = Category::factory()->create(['name' => 'existing category name']);

        $response = $this->putJson(route('api.v1.categories.update', $category), [
            'name' => 'existing category name',
        ]);

        $response->assertOk();
        $response->assertExactJson([
            'message' => 'Категория обновлена',
            'data' => [
                'id' => $category->getKey(),
                'name' => 'existing category name',
                'parent_category' => null,
                'transactions_credit' => [
                    'amount' => 0,
                    'count' => 0,
                ],
                'transactions_debit' => [
                    'amount' => 0,
                    'count' => 0,
                ],
                'transactions_transfer' => [
                    'amount' => 0,
                    'count' => 0,
                ],
                'created_at' => $category->created_at,
                'updated_at' => $category->updated_at,
            ],
        ]);
    }

    public function testCategoryCanBeCreatedIfAnotherUserHasTheSameCategoryName(): void
    {
        $this->userLogin();
        Category::factory()->create(['name' => 'category 1']);

        $this->userLogin();
        $response = $this->postJson(route('api.v1.categories.store'), [
            'name' => 'category 1',
        ]);

        $this->assertCount(
            2,
            Category::query()->withoutGlobalScope(OwnerScope::class)->where('name', 'category 1')->get()
        );

        $response->assertCreated();
        $response->assertExactJson([
            'message' => 'Категория создана',
        ]);
    }

    public static function invalidCategoryDataProvider(): array
    {
        return [
            'wrong_data_1' => [
                'request' => [],
                'errors' => [
                    'name' => ['Поле name обязательно.'],
                ],
            ],
            'wrong_data_2' => [
                'request' => [
                    'name' => 'existing category name',
                ],
                'errors' => [
                    'name' => ['Такое название уже существует.'],
                ],
            ],
            'wrong_data_3' => [
                'request' => [
                    'name' => 'child category',
                    'is_child' => true,
                ],
                'errors' => [
                    'parent_id' => ['Поле parent id обязательно.'],
                ],
            ],
            'wrong_data_4' => [
                'request' => [
                    'name' => 'child category',
                    'is_child' => true,
                    'parent_id' => 1000000,
                ],
                'errors' => [
                    'parent_id' => ['Такого значения не существует.'],
                ],
            ],
        ];
    }
}
