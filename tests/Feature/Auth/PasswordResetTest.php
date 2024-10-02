<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['email' => 'test@test.test']);
    }

    public function testResetPasswordLinkCanBeRequested(): void
    {
        Notification::fake();

        $response = $this->postJson(route('api.v1.password.email'), ['email' => $this->user->email]);

        Notification::assertSentTo($this->user, ResetPassword::class, function ($notification) {
            return $notification->toMail($this->user)->subject === 'Оповещение о сбросе пароля';
        });

        $response
            ->assertOk()
            ->assertExactJson([
                'message' => 'Ссылка на сброс пароля была отправлена.',
            ])
        ;
    }

    #[DataProvider('invalidForgotPasswordDataProvider')]
    public function testResetPasswordLinkCanNotBeRequestedWithWrongData(array $request, array $errors): void
    {
        $response = $this->postJson(route('api.v1.password.email'), $request);

        $response->assertUnprocessable();
        $response->assertExactJson([
            'message' => 'Заполните форму правильно',
            'errors' => $errors,
        ]);
    }

    public function testPasswordCanBeResetWithValidToken(): void
    {
        Notification::fake();

        $this->postJson(route('api.v1.password.email'), ['email' => $this->user->email]);

        Notification::assertSentTo($this->user, ResetPassword::class, function (object $notification) {
            $response = $this->post(route('password.reset'), [
                'token' => $notification->token,
                'email' => $this->user->email,
                'password' => 'password',
                'password_confirmation' => 'password',
            ]);

            $response
                ->assertSessionHasNoErrors()
                ->assertOk()
                ->assertExactJson([
                    'message' => 'Ваш пароль был сброшен.',
                ])
            ;

            return true;
        });
    }

    #[DataProvider('invalidNewPasswordDataProvider')]
    public function testPasswordCanNotBeResetWithInvalidData(array $request, array $errors): void
    {
        $response = $this->post(route('password.reset'), $request);

        $response->assertUnprocessable();
        $response->assertExactJson([
            'message' => 'Заполните форму правильно',
            'errors' => $errors,
        ]);

    }

    public static function invalidForgotPasswordDataProvider(): array
    {
        return [
            'wrong_data_1' => [
                'request' => [],
                'errors' => [
                    'email' => ['Поле Email обязательно.'],
                ],
            ],
            'wrong_data_2' => [
                'request' => [
                    'email' => 'testexample',
                ],
                'errors' => [
                    'email' => ['Значение поля Email должно быть действительным электронным адресом.'],
                ],
            ],
            'wrong_data_3' => [
                'request' => [
                    'email' => 'TEsT@TeST.test',
                ],
                'errors' => [
                    'email' => ['Значение поля Email должно быть в нижнем регистре.'],
                ],
            ],
            'wrong_data_4' => [
                'request' => [
                    'email' => 'test@example',
                ],
                'errors' => [
                    'email' => ['Значение поля Email должно быть действительным электронным адресом.'],
                ],
            ],
            'wrong_data_5' => [
                'request' => [
                    'email' => 'email@not.exist',
                ],
                'errors' => [
                    'email' => ['Не удалось найти пользователя с указанным электронным адресом.'],
                ],
            ],
        ];
    }

    public static function invalidNewPasswordDataProvider(): array
    {
        return [
            'wrong_data_1' => [
                'request' => [],
                'errors' => [
                    'token' => ['Поле token обязательно.'],
                    'email' => ['Поле Email обязательно.'],
                    'password' => ['Поле Пароль обязательно.'],
                ],
            ],
            'wrong_data_2' => [
                'request' => [
                    'token' => '123qweasdzxc',
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
                    'token' => '223344eewwqq',
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
                    'token' => 'aassddqqwweerr',
                    'email' => 'test@example.TEST',
                    'password' => '123456789',
                    'password_confirmation' => '123456789',
                ],
                'errors' => [
                    'email' => ['Значение поля Email должно быть в нижнем регистре.'],
                ],
            ],
            'wrong_data_5' => [
                'request' => [
                    'token' => 'aassddqqwweerr',
                    'email' => 'email@not.exist',
                    'password' => '123456789',
                    'password_confirmation' => '123456789',
                ],
                'errors' => [
                    'email' => ['Не удалось найти пользователя с указанным электронным адресом.'],
                ],
            ],
            'wrong_data_6' => [
                'request' => [
                    'token' => 'aassddqqwweerr',
                    'email' => 'test@test.test',
                    'password' => '123456789',
                    'password_confirmation' => '123456789',
                ],
                'errors' => [
                    'email' => ['Ошибочный код сброса пароля.'],
                ],
            ],
        ];
    }
}
