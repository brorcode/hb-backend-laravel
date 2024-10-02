<?php

namespace Tests\Unit;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HorizonAccessTest extends TestCase
{
    use RefreshDatabase;

    public function testHorizonIsProtected(): void
    {
        $response = $this->get('/horizon');
        $response->assertForbidden();
    }

    public function testUserCanAccessHorizonWithCorrectPermissions(): void
    {
        /** @var Role $role */
        $role = Role::factory()->withName('some role name')->create();
        $permission = Permission::factory()->withName(Permission::NAME_HORIZON_VIEW)->create();
        $role->givePermissionTo($permission);

        /** @var User $user */
        $user = User::factory()->create();
        $user->assignRole($role);

        $this->actingAs($user);

        $response = $this->get('/horizon');
        $response->assertOk();
    }
}
