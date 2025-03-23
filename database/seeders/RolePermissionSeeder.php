<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
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
                Permission::NAME_LOANS_VIEW,
                Permission::NAME_LOANS_EDIT,
                Permission::NAME_CATEGORY_POINTERS_VIEW,
                Permission::NAME_CATEGORY_POINTERS_EDIT,
                Permission::NAME_BUDGETS_VIEW,
                Permission::NAME_BUDGETS_EDIT,
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
                Permission::NAME_LOANS_VIEW,
                Permission::NAME_LOANS_EDIT,
                Permission::NAME_CATEGORY_POINTERS_VIEW,
                Permission::NAME_CATEGORY_POINTERS_EDIT,
                Permission::NAME_BUDGETS_VIEW,
                Permission::NAME_BUDGETS_EDIT,
            ],
            Role::NAME_DEMO_USER => [
                Permission::NAME_TRANSACTIONS_VIEW,
                Permission::NAME_CATEGORIES_VIEW,
                Permission::NAME_ACCOUNTS_VIEW,
                Permission::NAME_TAGS_VIEW,
                Permission::NAME_LOANS_VIEW,
                Permission::NAME_CATEGORY_POINTERS_VIEW,
                Permission::NAME_BUDGETS_VIEW,
            ],
        ];

        collect($rolePermissions)->each(function ($permissions, $roleName) {
            if ($role = Role::query()->where('name', $roleName)->first()) {
                $role->syncPermissions($permissions);
            }
        });
    }
}
