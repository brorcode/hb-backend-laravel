<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use DatabaseMigrations;

    private User $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    public function testUserCanAuthenticateUsingApi(): void
    {
        $response = $this->postJson(route('api.v1.login'), [
            'email' => $this->user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertOk();
        $response->assertExactJson([
            'data' => [
                'name' => $this->user->name,
                'email' => $this->user->email,
                'has_verified_email' => $this->user->hasVerifiedEmail(),
                'permissions' => [],
            ],
        ]);
    }

    public function testUserCanNotAuthenticateWithMissedData(): void
    {
        $response = $this->postJson(route('api.v1.login'));

        $response->assertUnprocessable();
        $response->assertExactJson([
            'message' => 'Заполните форму правильно',
            'errors' => [
                'email' => [
                    'Поле Email обязательно.'
                ],
                'password' => [
                    'Поле Пароль обязательно.'
                ],
            ],
        ]);
    }

    public function testUserCanNotAuthenticateWithInvalidPassword(): void
    {
        $response = $this->postJson(route('api.v1.login'), [
            'email' => $this->user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
        $response->assertUnprocessable();
        $response->assertExactJson([
            'message' => 'Заполните форму правильно',
            'errors' => [
                'email' => [
                    'Неверное имя пользователя или пароль.'
                ],
            ],
        ]);
    }

    public function testUserCanNotAuthenticateWithRateLimitsAttempts()
    {
        for ($i = 0; $i < 5; $i++) {
            $response = $this->postJson(route('api.v1.login'), [
                'email' => $this->user->email,
                'password' => 'wrong-password',
            ]);
            $response->assertUnprocessable();
        }

        // After 5 failed attempts, check if rate limiting is in effect
        $response = $this->postJson(route('api.v1.login'), [
            'email' => $this->user->email,
            'password' => 'wrong-password',
            'remember' => false,
        ]);

        $response->assertUnprocessable();
        $response->assertExactJson([
            'message' => 'Заполните форму правильно',
            'errors' => [
                'email' => [
                    'Слишком много попыток входа. Пожалуйста, попробуйте позже.'
                ],
            ],
        ]);
    }

    public function testUserCanLogout(): void
    {
        $response = $this->actingAs($this->user)->postJson(route('api.v1.logout'));

        $this->assertGuest();
        $response->assertNoContent();
    }
}
