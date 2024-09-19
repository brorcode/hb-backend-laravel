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

    public function testUsersCanAuthenticateUsingApi(): void
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
            ],
        ]);
    }

    public function testUsersCanNotAuthenticateWithMissedData(): void
    {
        $response = $this->postJson(route('api.v1.login'));

        $response->assertUnprocessable();
        $response->assertExactJson([
            'message' => 'Заполните форму правильно',
            'errors' => [
                'email' => [
                    'Поле email обязательно.'
                ],
                'password' => [
                    'Поле пароль обязательно.'
                ],
            ],
        ]);
    }

    public function testUsersCanNotAuthenticateWithInvalidPassword(): void
    {
        $this->postJson(route('api.v1.logout'), [
            'email' => $this->user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function testUsersCanLogout(): void
    {
        $response = $this->actingAs($this->user)->postJson(route('api.v1.logout'));

        $this->assertGuest();
        $response->assertNoContent();
    }
}
