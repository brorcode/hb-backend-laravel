<?php

namespace Tests;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

trait HbTestTrait
{
    protected function userLogin($attributes = []): User
    {
        $this->seed(RolePermissionSeeder::class);

        /** @var User $user */
        $user = User::factory()->create($attributes);
        $user->syncRoles(Role::NAME_SUPER_USER);

        $this->actingAs($user);

        return $user;
    }
}
