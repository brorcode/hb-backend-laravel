<?php

namespace Tests\Feature\Auth;

use App\Events\UserRegistered;
use App\Models\Role;
use App\Models\User;
use App\Notifications\VerifyEmail;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function testNewUsersCanRegister(): void
    {
        $this->seed(RolePermissionSeeder::class);
        Event::fake();

        $this->assertCount(0, User::all());
        $response = $this->postJson(route('api.v1.register'), $this->getRequestData());
        $this->assertCount(1, User::all());
        $user = User::query()->first();

        $this->assertCount(1, $user->roles);
        $this->assertEquals(Role::NAME_USER, $user->roles->first()->name);

        Event::assertDispatched(UserRegistered::class);

        $this->assertAuthenticated();
        $response->assertExactJson($this->getResponseData($user));
    }

    public function testUserRegisteredEventRunsNotificationSending(): void
    {
        $this->seed(RolePermissionSeeder::class);
        Notification::fake();

        $this->assertCount(0, User::all());
        $response = $this->postJson(route('api.v1.register'), $this->getRequestData());
        $this->assertCount(1, User::all());
        $user = User::query()->first();

        Notification::assertSentTo($user, VerifyEmail::class, function ($notification) use ($user) {
            return $notification->toMail($user)->subject === 'Подтвердить адрес электронной почты';
        });

        $this->assertAuthenticated();
        $response->assertExactJson($this->getResponseData($user));
    }

    #[DataProvider('invalidDataProvider')]
    public function testUserCanNotRegisterWithInvalidData(array $request, array $errors): void
    {
        $this->assertCount(0, User::all());
        $response = $this->postJson(route('api.v1.register'), $request);

        $this->assertCount(0, User::all());

        $response->assertUnprocessable();
        $response->assertExactJson([
            'message' => 'Заполните форму правильно',
            'errors' => $errors,
        ]);
    }

    public static function invalidDataProvider(): array
    {
        return [
            'wrong_data_1' => [
                'request' => [],
                'errors' => [
                    'name' => ['Поле Имя обязательно.'],
                    'email' => ['Поле Email обязательно.'],
                    'password' => ['Поле Пароль обязательно.'],
                ],
            ],
            'wrong_data_2' => [
                'request' => [
                    'name' => 'Test User',
                    'email' => 'testexample',
                    'password' => '123',
                    'password_confirmation' => '321',
                ],
                'errors' => [
                    'email' => ['Значение поля Email должно быть действительным электронным адресом.'],
                    'password' => [
                        'Значение поля Пароль не совпадает с подтверждаемым.',
                        'Количество символов в поле Пароль должно быть не меньше 8.'
                    ],
                ],
            ],
            'wrong_data_3' => [
                'request' => [
                    'name' => 'Test User',
                    'email' => 'test@example',
                    'password' => '123456789',
                    'password_confirmation' => '123456789',
                ],
                'errors' => [
                    'email' => ['Значение поля Email должно быть действительным электронным адресом.'],
                ],
            ],
            'wrong_data_4' => [
                'request' => [
                    'name' => 'Test User',
                    'email' => 'test@example.TEST',
                    'password' => '123456789',
                    'password_confirmation' => '123456789',
                ],
                'errors' => [
                    'email' => ['Значение поля Email должно быть в нижнем регистре.'],
                ],
            ],
        ];
    }

    private function getRequestData(): array
    {
        return [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];
    }

    private function getResponseData(User $user): array
    {
        return [
            'message' => 'Ссылка для потверждения была отправлена на вашу почту',
            'data' => [
                'name' => $user->name,
                'email' => $user->email,
                'has_verified_email' => false,
                'permissions' => $user->getPermissionsViaRoles()->pluck('name')->toArray(),
            ],
        ];
    }
}
