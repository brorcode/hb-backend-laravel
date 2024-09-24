<?php

namespace Tests\Feature\Category;

use App\Models\Category;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CategoryUpsertTest extends TestCase
{
    use DatabaseMigrations;

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
                'created_at' => $category->created_at,
                'updated_at' => $category->updated_at,
            ],
        ]);
    }

    public function testCategoryStore(): void
    {
        $this->assertCount(0, Category::all());

        $response = $this->postJson(route('api.v1.categories.store'), [
            'name' => 'test',
        ]);

        $this->assertCount(1, Category::all());
        $this->assertDatabaseHas((new Category())->getTable(), [
            'name' => 'test',
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
        /** @var Category $category */
        $category = Category::factory()->create(['name' => 'test category name']);
        $this->assertCount(1, Category::all());
        $this->assertDatabaseMissing((new Category())->getTable(), [
            'name' => 'new category name',
        ]);

        $response = $this->putJson(route('api.v1.categories.update', $category), [
            'name' => 'new category name',
        ]);

        $this->assertCount(1, Category::all());
        $this->assertDatabaseHas((new Category())->getTable(), [
            'name' => 'new category name',
        ]);

        $response->assertOk();

        $freshCategory = $category->fresh();
        $response->assertExactJson([
            'message' => 'Категория обновлена',
            'data' => [
                'id' => $freshCategory->getKey(),
                'name' => 'new category name',
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
                    'name' => ['Такое значение поля name уже существует.'],
                ],
            ],
        ];
    }
}
