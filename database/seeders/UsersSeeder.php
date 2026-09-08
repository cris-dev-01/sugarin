<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        collect([
            (object) [
                'data' => [
                    'name' => 'Administrador',
                    'email' => 'admin@example.org',
                    'password' => Hash::make('password'),
                ],
                'role' => 'Administrator',
            ],
            (object) [
                'data' => [
                    'name' => 'Usuario',
                    'email' => 'user@example.org',
                    'password' => Hash::make('password'),
                ],
                'role' => 'Patient',
            ],
            
        ])->each(function (object $userData) {
            User::query()->create($userData->data)->assignRole($userData->role);
        });
    }
}
