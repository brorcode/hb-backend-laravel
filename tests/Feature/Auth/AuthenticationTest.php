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

    public function testUsersCanAuthenticateUsingTheLoginScreen(): void
    {
        $response = $this->postJson(route('login'), [
            'email' => $this->user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertOk();
        $response->assertExactJson([
            'name' => $this->user->name,
            'email' => $this->user->email,
        ]);
    }

    public function testUsersCanNotAuthenticateWithInvalidPassword(): void
    {
        $this->postJson('/login', [
            'email' => $this->user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function testUsersCanLogout(): void
    {
        $response = $this->actingAs($this->user)->postJson('/logout');

        $this->assertGuest();
        $response->assertNoContent();
    }
}
