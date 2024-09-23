<?php

namespace App\Console\Commands;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Console\Command;

class CreateRolePermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-role-permissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info(now()->toDateTimeString().' Starting CreateRolePermissions Command.');

        $rolesCount = 0;
        $permissionsCount = 0;

        collect(Role::NAMES)->each(function ($roleName) use (&$rolesCount) {
            if (!Role::query()->where('name', $roleName)->exists()) {
                Role::create(['name' => $roleName]);
                $rolesCount++;
            }
        });

        collect(Permission::NAMES)->each(function ($permissionName) use (&$permissionsCount) {
            if (!Permission::query()->where('name', $permissionName)->exists()) {
                Permission::create(['name' => $permissionName]);
                $permissionsCount++;
            }
        });

        $this->assignPermissionsToRoles();

        $this->info('Roles created: ' . $rolesCount);
        $this->info('Permissions created: ' . $permissionsCount);
        $this->info(now()->toDateTimeString().' Ending CreateRolePermissions Command.');
    }

    protected function assignPermissionsToRoles(): void
    {
        $rolePermissions = [
            Role::NAME_SUPER_USER => [
                Permission::NAME_HORIZON_VIEW,
                Permission::NAME_USERS_VIEW,
                Permission::NAME_USERS_EDIT,
                Permission::NAME_TRANSACTIONS_VIEW,
                Permission::NAME_TRANSACTIONS_EDIT,
                Permission::NAME_CATEGORIES_VIEW,
                Permission::NAME_CATEGORIES_EDIT,
                Permission::NAME_ACCOUNTS_VIEW,
                Permission::NAME_ACCOUNTS_EDIT,
                Permission::NAME_TAGS_VIEW,
                Permission::NAME_TAGS_EDIT,
                Permission::NAME_CATEGORY_POINTERS_VIEW,
                Permission::NAME_CATEGORY_POINTERS_EDIT,
                Permission::NAME_PROFILE_VIEW,
                Permission::NAME_PROFILE_EDIT,
            ],
            Role::NAME_USER => [
                Permission::NAME_TRANSACTIONS_VIEW,
                Permission::NAME_TRANSACTIONS_EDIT,
                Permission::NAME_CATEGORIES_VIEW,
                Permission::NAME_CATEGORIES_EDIT,
                Permission::NAME_ACCOUNTS_VIEW,
                Permission::NAME_ACCOUNTS_EDIT,
                Permission::NAME_TAGS_VIEW,
                Permission::NAME_TAGS_EDIT,
                Permission::NAME_CATEGORY_POINTERS_VIEW,
                Permission::NAME_CATEGORY_POINTERS_EDIT,
                Permission::NAME_PROFILE_VIEW,
                Permission::NAME_PROFILE_EDIT,
            ],
            Role::NAME_DEMO_USER => [
                Permission::NAME_TRANSACTIONS_VIEW,
                Permission::NAME_CATEGORIES_VIEW,
                Permission::NAME_ACCOUNTS_VIEW,
                Permission::NAME_TAGS_VIEW,
                Permission::NAME_CATEGORY_POINTERS_VIEW,
                Permission::NAME_PROFILE_VIEW,
            ],
            Role::NAME_NOT_VERIFIED_USER => [
                Permission::NAME_PROFILE_VIEW,
                Permission::NAME_PROFILE_EDIT,
            ],
        ];

        collect($rolePermissions)->each(function ($permissions, $roleName) {
            if ($role = Role::query()->where('name', $roleName)->first()) {
                $role->syncPermissions($permissions);
            }
        });
    }
}
