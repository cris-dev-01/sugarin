<?php

declare(strict_types=1);

namespace Database\Seeders;

use Spatie\Permission\Models\Role;
use Illuminate\Database\Seeder;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        collect([
            ['name' => 'Administrator'],
            ['name' => 'Patient'],
        ])->each(function (array $role) {
            Role::query()->create($role);
        });
    }
}
