<?php

namespace Tests\Feature\Tag;

use App\Models\Tag;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class TagListTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->userLogin();
    }

    public function testTagListApiReturnsCorrectResponse(): void
    {
        $tags = Tag::factory(11)
            ->has(Transaction::factory()->count(3)->state([
                'amount' => 1000,
                'is_debit' => true,
                'is_transfer' => false,
            ]))
            ->create()
        ;
        $data = $tags->take(10)->map(function (Tag $tag) {
            return [
                'id' => $tag->getKey(),
                'name' => $tag->name,
                'amount' => 3*10,
                'created_at' => $tag->created_at,
                'updated_at' => $tag->updated_at,
            ];
        });

        $response = $this->postJson(route('api.v1.tags.index'), [
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
    public function testTagListApiReturnsValidationErrors(array $request): void
    {
        $response = $this->postJson(route('api.v1.tags.index'), $request);
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
