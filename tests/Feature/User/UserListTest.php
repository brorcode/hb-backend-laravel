<?php

namespace Tests\Feature\User;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class UserListTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->userLogin();
    }

    public function testUserListApiReturnsCorrectResponse(): void
    {
        User::factory(10)->create();
        $data = User::query()->take(10)->get()->map(function (User $user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'has_verified_email' => $user->hasVerifiedEmail(),
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ];
        });

        $response = $this->postJson(route('api.v1.users.index'), [
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
    public function testUserListApiReturnsValidationErrors(array $request): void
    {
        $response = $this->postJson(route('api.v1.users.index'), $request);
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
