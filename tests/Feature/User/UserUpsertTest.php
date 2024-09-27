<?php

namespace Tests\Feature\User;

use App\Exceptions\ApiBadRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class UserUpsertTest extends TestCase
{
    use DatabaseMigrations;

    private User $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = $this->userLogin([
            'email' => 'user@email.test',
            'password' => 'testPassword12345',
        ]);
    }

    public function testUserShow(): void
    {
        $response = $this->getJson(route('api.v1.users.show', $this->user));

        $response->assertOk();
        $response->assertExactJson([
            'data' => [
                'id' => $this->user->getKey(),
                'name' => $this->user->name,
                'email' => $this->user->email,
                'has_verified_email' => $this->user->hasVerifiedEmail(),
                'created_at' => $this->user->created_at,
                'updated_at' => $this->user->updated_at,
            ],
        ]);
    }

    public function testUserStore(): void
    {
        $this->assertCount(1, User::all());
        $this->assertDatabaseMissing((new User())->getTable(), [
            'name' => 'test',
            'email' => 'test@example.com',
        ]);

        $response = $this->postJson(route('api.v1.users.store'), [
            'name' => 'test',
            'email' => 'test@example.com',
            'password' => 'test12345',
            'password_confirmation' => 'test12345',
        ]);

        $this->assertCount(2, User::all());
        $this->assertDatabaseHas((new User())->getTable(), [
            'name' => 'test',
            'email' => 'test@example.com',
        ]);

        $createdUser = User::query()->where('email', 'test@example.com')->first();
        $this->assertTrue(Hash::check('test12345', $createdUser->password));

        $response->assertCreated();
        $response->assertExactJson([
            'message' => 'Пользователь создан',
        ]);
    }

    #[DataProvider('invalidCreateUserDataProvider')]
    public function testUserCanNotBeStoredWithInvalidData(array $request, array $errors): void
    {
        $response = $this->postJson(route('api.v1.users.store'), $request);

        $response->assertUnprocessable();
        $response->assertExactJson([
            'message' => 'Заполните форму правильно',
            'errors' => $errors,
        ]);
    }

    public function testUserUpdate(): void
    {
        $this->assertCount(1, User::all());
        $this->assertDatabaseMissing((new User())->getTable(), [
            'name' => 'test',
            'email' => 'test@example.com',
        ]);

        $response = $this->putJson(route('api.v1.users.update', $this->user), [
            'name' => 'test',
            'email' => 'test@example.com',
            'password' => 'test12345',
            'password_confirmation' => 'test12345',
        ]);

        $this->assertCount(1, User::all());
        $this->assertDatabaseHas((new User())->getTable(), [
            'name' => 'test',
            'email' => 'test@example.com',
        ]);

        $freshUser = $this->user->fresh();
        $this->assertTrue(Hash::check('test12345', $freshUser->password));
        $response->assertOk();

        $response->assertExactJson([
            'message' => 'Пользователь обновлен',
            'data' => [
                'id' => $freshUser->getKey(),
                'name' => 'test',
                'email' => 'test@example.com',
                'has_verified_email' => $freshUser->hasVerifiedEmail(),
                'created_at' => $freshUser->created_at,
                'updated_at' => $freshUser->updated_at,
            ],
        ]);
    }

    #[DataProvider('invalidUpdateUserDataProvider')]
    public function testUserCanNotBeUpdatedWithInvalidData(array $request, array $errors): void
    {
        $userForUpdate = User::factory()->create(['email' => 'userForUpdate@email.test']);

        $response = $this->putJson(route('api.v1.users.update', $userForUpdate), $request);

        $response->assertUnprocessable();
        $response->assertExactJson([
            'message' => 'Заполните форму правильно',
            'errors' => $errors,
        ]);
    }

    public static function invalidCreateUserDataProvider(): array
    {
        $dataProvider = [
            'wrong_data_1' => [
                'request' => [],
                'errors' => [
                    'name' => ['Поле name обязательно.'],
                    'email' => ['Поле email обязательно.'],
                    'password' => ['Поле password обязательно.'],
                ],
            ],
        ];

        return array_merge($dataProvider, self::commonValidationAssertions());
    }

    public static function invalidUpdateUserDataProvider(): array
    {
        $dataProvider = [
            'wrong_data_1' => [
                'request' => [],
                'errors' => [
                    'name' => ['Поле name обязательно.'],
                    'email' => ['Поле email обязательно.'],
                ],
            ],
        ];

        return array_merge($dataProvider, self::commonValidationAssertions());
    }

    private static function commonValidationAssertions(): array
    {
        return [
            'wrong_data_2' => [
                'request' => [
                    'name' => 'test',
                    'email' => 'test',
                    'password' => 'test',
                ],
                'errors' => [
                    'email' => ['Значение поля email должно быть действительным электронным адресом.'],
                    'password' => [
                        'Значение поля password не совпадает с подтверждаемым.',
                        'Количество символов в поле password должно быть не меньше 8.'
                    ],
                ],
            ],
            'wrong_data_3' => [
                'request' => [
                    'name' => 'test',
                    'email' => 'user@email.test',
                    'password' => 'test12345',
                    'password_confirmation' => 'test12345',
                ],
                'errors' => [
                    'email' => ['Такое значение поля email уже существует.'],
                ],
            ],
        ];
    }
}
