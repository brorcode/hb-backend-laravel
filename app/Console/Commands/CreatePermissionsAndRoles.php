<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Console\Command;

class CreatePermissionsAndRoles extends Command
{
    protected $signature = 'app:create-permissions-and-roles';
    protected $description = 'One time command to recreate permissions and roles and assign them to the super user and demo user.';

    public function handle(): void
    {
        $this->call(RolePermissionSeeder::class);
        $this->info('Permissions and roles have been created.');

        if ($superUser = User::query()->where('email', config('homebudget.super_user_email'))->first()) {
            $superUser->syncRoles(Role::NAME_SUPER_USER);
        }

        if ($demoUser = User::query()->where('email', config('homebudget.demo_user_email'))->first()) {
            $demoUser->syncRoles(Role::NAME_DEMO_USER);
        }

        $this->info('Super user and demo user have been given permissions.');
    }
}
