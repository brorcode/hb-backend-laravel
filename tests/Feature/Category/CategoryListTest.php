<?php

namespace Tests\Feature\Category;

use App\Models\Category;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CategoryListTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp(): void
    {
        parent::setUp();

        $this->userLogin();
    }

    public function testCategoryListApiReturnsCorrectResponse(): void
    {
        $categories = Category::factory(11)->create();
        $data = $categories->take(10)->map(function (Category $category) {
            return [
                'id' => $category->id,
                'name' => $category->name,
                'created_at' => $category->created_at,
                'updated_at' => $category->updated_at,
            ];
        });

        $response = $this->postJson(route('api.v1.categories.index'), [
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
    public function testCategoryListApiReturnsValidationErrors(array $request): void
    {
        $response = $this->postJson(route('api.v1.categories.index'), $request);
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
