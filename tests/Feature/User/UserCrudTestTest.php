<?php

namespace Tests\Feature\User;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class UserCrudTestTest extends TestCase
{
    use DatabaseMigrations;

    private User $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function testUserListApiReturnsValidationErrors(): void
    {
        $response = $this->postJson(route('api.v1.users.index'));

        $response->assertUnprocessable();
        $response->assertExactJson([
            'message' => 'Заполните форму правильно.',
            'errors' => [
                'page' => [
                    'The page field is required.'
                ],
                'limit' => [
                    'The limit field is required.'
                ],
                'sorting' => [
                    'The sorting field is required.',
                ]
            ],
        ]);
    }
}
