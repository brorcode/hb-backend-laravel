<?php

namespace Tests\Feature\CategoryPointer;

use App\Jobs\UpdateTransactionCategoriesJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CategoryPointerSaveTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->userLogin();
    }

    public function testCategoryPointerSave(): void
    {
        Queue::fake();

        $response = $this->postJson(route('api.v1.category-pointers.save'), [
            'parent' => [
                [
                    'name' => 'parent pointer',
                    'tags_array' => ['tag1', 'tag2'],
                ],
            ],
            'child' => [
                [
                    'name' => 'child pointer',
                    'tags_array' => ['tag1', 'tag2'],
                ],
            ],
        ]);

        Queue::assertPushed(UpdateTransactionCategoriesJob::class);

        $response->assertOk();
        $response->assertExactJson([
            'message' => 'Указатели категорий обновлены',
            'parent' => [
                [
                    'name' => 'parent pointer',
                    'is_parent' => true,
                    'tags_array' => ['tag1', 'tag2'],
                ],
            ],
            'child' => [
                [
                    'name' => 'child pointer',
                    'is_parent' => false,
                    'tags_array' => ['tag1', 'tag2'],
                ]
            ],
        ]);
    }

    #[DataProvider('invalidCategoryPointerDataProvider')]
    public function testCategoryPointerSaveValidation(array $request, array $errors): void
    {
        $response = $this->postJson(route('api.v1.category-pointers.save'), $request);

        $response->assertUnprocessable();
        $response->assertExactJson([
            'message' => 'Заполните форму правильно',
            'errors' => $errors,
        ]);
    }

    public static function invalidCategoryPointerDataProvider(): array
    {
        return [
            'wrong_data_1' => [
                'request' => [],
                'errors' => [
                    'parent' => ['Поле parent обязательно.'],
                    'child' => ['Поле child обязательно.'],
                ],
            ],
            'wrong_data_2' => [
                'request' => [
                    'parent' => true,
                    'child' => 'test',
                ],
                'errors' => [
                    'parent' => ['Значение поля parent должно быть массивом.'],
                    'child' => ['Значение поля child должно быть массивом.'],
                ],
            ],
            'wrong_data_3' => [
                'request' => [
                    'parent' => [[]],
                    'child' => [[]],
                ],
                'errors' => [
                    'parent.0.name' => ['Поле parent.0.name обязательно.'],
                    'parent.0.tags_array' => ['Поле parent.0.tags_array обязательно.'],
                    'child.0.name' => ['Поле child.0.name обязательно.'],
                    'child.0.tags_array' => ['Поле child.0.tags_array обязательно.'],
                ],
            ],
            'wrong_data_4' => [
                'request' => [
                    'parent' => [
                        [
                            'name' => 'test parent',
                            'tags_array' => 'test',
                        ]
                    ],
                    'child' => [
                        [
                            'name' => 'test child',
                            'tags_array' => false,
                        ]
                    ],
                ],
                'errors' => [
                    'parent.0.tags_array' => ['Значение поля parent.0.tags_array должно быть массивом.'],
                    'child.0.tags_array' => ['Значение поля child.0.tags_array должно быть массивом.'],
                ],
            ],
            'wrong_data_5' => [
                'request' => [
                    'parent' => [
                        [
                            'name' => 'test parent',
                            'tags_array' => ['tag 1', 'tag 1'],
                        ]
                    ],
                    'child' => [
                        [
                            'name' => 'test child',
                            'tags_array' => ['tag 2', 'tag 3'],
                        ]
                    ],
                ],
                'errors' => [
                    'category_pointer_tag_id' => ['Каждый тег должен иметь уникальное имя. Дубликат: tag 1.'],
                ],
            ],
            'wrong_data_6' => [
                'request' => [
                    'parent' => [
                        [
                            'name' => 'test parent',
                            'tags_array' => ['tag 1', 'tag 2'],
                        ]
                    ],
                    'child' => [
                        [
                            'name' => 'test child',
                            'tags_array' => ['tag 3', 'tag 3'],
                        ]
                    ],
                ],
                'errors' => [
                    'category_pointer_tag_id' => ['Каждый тег должен иметь уникальное имя. Дубликат: tag 3.'],
                ],
            ],
            'wrong_data_7' => [
                'request' => [
                    'parent' => [
                        [
                            'name' => 'test tag name',
                            'tags_array' => ['tag 1', 'tag 2'],
                        ]
                    ],
                    'child' => [
                        [
                            'name' => 'test tag name',
                            'tags_array' => ['tag 3', 'tag 4'],
                        ]
                    ],
                ],
                'errors' => [
                    'category_pointer_id' => ['Каждая категория должна иметь уникальное имя. Дубликат: test tag name.'],
                ],
            ],
        ];
    }
}
