<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        collect([
            ['name' => 'show-dashboard', 'guard_name' => 'web'],
            ['name' => 'show-summary', 'guard_name' => 'web'],
            ['name' => 'create-glucose-logs', 'guard_name' => 'web'],
            ['name' => 'show-glucose-logs', 'guard_name' => 'web'],
        ])->each(function (array $permission) {
            Permission::query()->create($permission);
        });
    }
}
