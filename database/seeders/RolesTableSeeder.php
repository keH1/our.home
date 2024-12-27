<?php

namespace Database\Seeders;

use App\Enums\Roles;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesTableSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            Roles::ADMIN->value => Roles::ADMIN->permissions(),
            Roles::USER->value => Roles::USER->permissions(),
            Roles::GUEST->value => Roles::GUEST->permissions(),
        ];

        foreach ($roles as $roleName => $perms) {
            $role = Role::firstOrCreate(['name' => $roleName]);

            foreach ($perms as $permName) {
                $permission = Permission::firstOrCreate(['name' => $permName->value]);
                $role->givePermissionTo($permission);
                $permission->assignRole($role);
            }
        }
    }
}
