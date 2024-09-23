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
        $this->postJson(route('api.v1.logout'), [
            'email' => $this->user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function testUserCanLogout(): void
    {
        $response = $this->actingAs($this->user)->postJson(route('api.v1.logout'));

        $this->assertGuest();
        $response->assertNoContent();
    }
}
