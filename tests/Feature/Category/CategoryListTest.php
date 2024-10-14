<?php

namespace Tests\Feature\Category;

use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CategoryListTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->userLogin();
    }

    public function testParentCategoryListApiReturnsCorrectResponse(): void
    {
        Category::factory(11)->withParentCategory()
            ->has(Transaction::factory()->count(3)->sequence(
                ['amount' => 200, 'is_debit' => true, 'is_transfer' => false],
                ['amount' => -100, 'is_debit' => false, 'is_transfer' => false],
                ['amount' => -150, 'is_debit' => false, 'is_transfer' => true],
            ))
            ->create()
        ;

        $parentCategories = Category::query()->whereNull('parent_id')->limit(10)->get();
        $data = $parentCategories->map(function (Category $category) {
            return [
                'id' => $category->getKey(),
                'name' => $category->name,
                'parent_category' => null,
                'transactions_credit' => [
                    'amount' => -100,
                    'count' => 1,
                ],
                'transactions_debit' => [
                    'amount' => 200,
                    'count' => 1,
                ],
                'transactions_transfer' => [
                    'amount' => -150,
                    'count' => 1,
                ],
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

    public function testChildCategoryListApiReturnsCorrectResponse(): void
    {
        /** @var Category $childCategory */
        $childCategory = Category::factory()->withParentCategory()
            ->has(Transaction::factory()->count(3)->sequence(
                ['amount' => 200, 'is_debit' => true, 'is_transfer' => false],
                ['amount' => -100, 'is_debit' => false, 'is_transfer' => false],
                ['amount' => -150, 'is_debit' => false, 'is_transfer' => true],
            ))
            ->create()
        ;

        $response = $this->postJson(route('api.v1.categories.child', $childCategory->parent_id), [
            'page' => 1,
            'limit' => 10,
        ]);

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertExactJson([
            'data' => [
                [
                    'id' => $childCategory->getKey(),
                    'name' => $childCategory->name,
                    'parent_category' => $childCategory->parentCategory->only('id', 'name'),
                    'transactions_credit' => [
                        'amount' => -100,
                        'count' => 1,
                    ],
                    'transactions_debit' => [
                        'amount' => 200,
                        'count' => 1,
                    ],
                    'transactions_transfer' => [
                        'amount' => -150,
                        'count' => 1,
                    ],
                    'created_at' => $childCategory->created_at,
                    'updated_at' => $childCategory->updated_at,
                ],
            ],
            'meta' => [
                'currentPage' => 1,
                'hasNextPage' => false,
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
