<?php

namespace Tests\Feature\UserProfile;

use App\Models\Account;
use App\Models\Transaction;
use App\Models\User;
use App\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class UserProfileTest extends TestCase
{
    use DatabaseMigrations;

    private User $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = $this->userLogin();
    }

    public function testUserProfileIndex(): void
    {
        $response = $this->getJson(route('api.v1.user.profile.index'));

        $response->assertOk();
        $response->assertExactJson([
            'data' => [
                'name' => $this->user->name,
                'email' => $this->user->email,
                'has_verified_email' => $this->user->hasVerifiedEmail(),
                'permissions' => $this->user->getPermissionsViaRoles()->pluck('name')->toArray(),
            ],
        ]);
    }

    public function testUserProfileUpdate(): void
    {
        $this->assertCount(1, User::all());
        $this->assertDatabaseMissing((new User())->getTable(), [
            'name' => 'new user name',
        ]);
        $this->assertFalse(Hash::check('test12345', $this->user->password));

        $response = $this->putJson(route('api.v1.user.profile.update', $this->user), [
            'email' => $this->user->email,
            'name' => 'new user name',
            'password' => 'test12345',
            'password_confirmation' => 'test12345',
        ]);

        $this->assertCount(1, User::all());
        $this->assertDatabaseHas((new User())->getTable(), [
            'name' => 'new user name',
        ]);
        $this->assertTrue(Hash::check('test12345', $this->user->password));

        $response->assertOk();

        $freshUser = $this->user->fresh();
        $response->assertExactJson([
            'message' => 'Пользователь обновлен',
            'data' => [
                'name' => $freshUser->name,
                'email' => $freshUser->email,
                'has_verified_email' => $freshUser->hasVerifiedEmail(),
                'permissions' => $freshUser->getPermissionsViaRoles()->pluck('name')->toArray(),
            ],
        ]);
    }

    public function testUserProfileUpdateEmailResetEmailVerification(): void
    {
        $this->assertTrue($this->user->hasVerifiedEmail());
        $this->assertDatabaseMissing((new User())->getTable(), [
            'email' => 'new@email.test',
        ]);
        $response = $this->putJson(route('api.v1.user.profile.update', $this->user), [
            'email' => 'new@email.test',
            'name' => $this->user->name,
        ]);

        $freshUser = $this->user->fresh();

        $this->assertFalse($freshUser->hasVerifiedEmail());
        $this->assertDatabaseHas((new User())->getTable(), [
            'email' => 'new@email.test',
        ]);

        $response->assertOk();
        $response->assertExactJson([
            'message' => 'Пользователь обновлен',
            'data' => [
                'name' => $freshUser->name,
                'email' => $freshUser->email,
                'has_verified_email' => false,
                'permissions' => $freshUser->getPermissionsViaRoles()->pluck('name')->toArray(),
            ],
        ]);
    }

    public function testUserCanRequestEmailVerification(): void
    {
        Notification::fake();

        $this->user->email_verified_at = null;
        $this->user->save();

        $this->assertFalse($this->user->hasVerifiedEmail());
        $response = $this->postJson(route('api.v1.user.profile.email.verification'));

        $response->assertOk();

        Notification::assertSentTo($this->user, VerifyEmail::class, function ($notification) {
            return $notification->toMail($this->user)->subject === 'Подтвердить адрес электронной почты';
        });

        $response->assertExactJson([
            'message' => 'Вам отправлено письмо для потверждения электронной почты',
        ]);
    }

    public function testUserCanNotVerifyEmailTwice(): void
    {
        $this->assertTrue($this->user->hasVerifiedEmail());
        $response = $this->postJson(route('api.v1.user.profile.email.verification'));

        $response->assertOk();
        $response->assertExactJson([
            'message' => 'Ваша почта уже потверждена',
            'data' => [
                'name' => $this->user->name,
                'email' => $this->user->email,
                'has_verified_email' => true,
                'permissions' => $this->user->getPermissionsViaRoles()->pluck('name')->toArray(),
            ],
        ]);
    }

    #[DataProvider('invalidAccountDataProvider')]
    public function testUserProfileCanNotBeUpdatedWithInvalidData(array $request, array $errors): void
    {
        User::factory()->create(['email' => 'existing@email.test']);

        $response = $this->putJson(route('api.v1.user.profile.update', $this->user), $request);

        $response->assertUnprocessable();
        $response->assertExactJson([
            'message' => 'Заполните форму правильно',
            'errors' => $errors,
        ]);
    }

    public static function invalidAccountDataProvider(): array
    {
        return [
            'wrong_data_1' => [
                'request' => [],
                'errors' => [
                    'name' => ['Поле name обязательно.'],
                    'email' => ['Поле email обязательно.'],
                ],
            ],
            'wrong_data_2' => [
                'request' => [
                    'name' => 'test',
                    'email' => 'test',
                    'password' => 'test',
                    'password_confirmation' => 'test',
                ],
                'errors' => [
                    'email' => ['Значение поля email должно быть действительным электронным адресом.'],
                    'password' => ['Количество символов в поле password должно быть не меньше 8.'],
                ],
            ],
            'wrong_data_3' => [
                'request' => [
                    'name' => 'test',
                    'email' => 'existing@email.test',
                    'password' => 'test12345',
                ],
                'errors' => [
                    'email' => ['Такое значение поля email уже существует.'],
                    'password' => ['Значение поля password не совпадает с подтверждаемым.'],
                ],
            ],
            'wrong_data_4' => [
                'request' => [
                    'name' => 'test',
                    'email' => 'test@email.test',
                    'password' => '123',
                    'password_confirmation' => '321',
                ],
                'errors' => [
                    'password' => [
                        'Значение поля password не совпадает с подтверждаемым.',
                        'Количество символов в поле password должно быть не меньше 8.'
                    ],
                ],
            ],
        ];
    }
}
