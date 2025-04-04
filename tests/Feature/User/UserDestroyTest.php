<?php

namespace Tests\Feature\User;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserDestroyTest extends TestCase
{
    use RefreshDatabase;

    public function testUserDestroy(): void
    {
        $this->userLogin();

        $users = User::factory(2)->create();
        $userToBeDeleted = $users->last();

        $this->assertCount(3, User::all());
        $this->assertDatabaseHas((new User())->getTable(), [
            'name' => $userToBeDeleted->name,
            'email' => $userToBeDeleted->email,
        ]);
        $response = $this->deleteJson(route('api.v1.users.destroy', $userToBeDeleted));

        $this->assertCount(2, User::all());
        $this->assertDatabaseMissing((new User())->getTable(), [
            'name' => $userToBeDeleted->name,
            'email' => $userToBeDeleted->email,
        ]);

        $response->assertOk();
        $response->assertExactJson([
            'message' => 'Пользователь удален',
        ]);
    }
}
