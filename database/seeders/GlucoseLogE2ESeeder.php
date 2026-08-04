<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\GlucoseRange;
use App\Models\User;
use App\Models\UserPatient;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeder de datos fijos para las pruebas E2E (Cypress) del flujo de registro
 * de glucosa. Pensado para correr únicamente contra la base de datos de
 * testing (sugarin_testing), nunca contra la base de datos de desarrollo.
 */
class GlucoseLogE2ESeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PermissionsSeeder::class,
            RolesSeeder::class,
            RolesPermissionsSeeder::class,
            StatusesSeeder::class,
        ]);

        $admin = User::query()->create([
            'name' => 'Admin E2E',
            'email' => 'e2e-admin@example.org',
            'password' => Hash::make('password'),
        ]);
        $admin->assignRole('Administrator');

        $range = GlucoseRange::query()->create([
            'alias' => 'Rango E2E',
            'min_fasting_value' => 70,
            'max_fasting_value' => 100,
            'min_non_fasting_value' => 70,
            'max_non_fasting_value' => 140,
        ]);

        $patientUser = User::query()->create([
            'name' => 'Paciente E2E',
            'email' => 'e2e-patient@example.org',
            'password' => Hash::make('password'),
        ]);
        $patientUser->assignRole('Patient');

        UserPatient::query()->create([
            'document_type' => 'rut',
            'document' => '11111111',
            'illness_found_at' => now()->subYear()->toDateString(),
            'initial_max_glucose_value' => 140,
            'user_id' => $patientUser->id,
            'glucose_range_id' => $range->id,
        ]);
    }
}
