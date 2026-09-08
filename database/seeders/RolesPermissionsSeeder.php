<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = Role::get();
        $adminPermissions = Permission::get();
        $guestPermissions = Permission::whereIn('name', ['show-summary', 'create-glucose-logs', 'show-glucose-logs'])->get();

        $roles->each(function ($role) use ($adminPermissions, $guestPermissions) {
            if ($role->name === 'Administrator') {
                $adminPermissions->each(function ($permission) use ($role) {
                    $role->givePermissionTo($permission);
                });
            }
            if ($role->name === 'Patient') {
                $guestPermissions->each(function ($permission) use ($role) {
                    $role->givePermissionTo($permission);
                });
            }
        });
    }
}
