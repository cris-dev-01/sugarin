<?php

declare(strict_types=1);

namespace Tests\Feature\Patients;

use App\Models\GlucoseRange;
use App\Models\User;
use App\Models\UserPatient;
use Database\Seeders\PermissionsSeeder;
use Database\Seeders\RolesPermissionsSeeder;
use Database\Seeders\RolesSeeder;
use Database\Seeders\SettingsSeeder;
use Database\Seeders\StatusesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchPatientsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([PermissionsSeeder::class, RolesSeeder::class, RolesPermissionsSeeder::class, StatusesSeeder::class, SettingsSeeder::class]);
    }

    private function actingAsAdministrator(): User
    {
        $admin = User::factory()->create();
        $admin->assignRole('Administrator');
        $this->actingAs($admin);

        return $admin;
    }

    private function createPatient(string $name, string $document): UserPatient
    {
        $range = GlucoseRange::factory()->create();
        $user = User::factory()->create(['name' => $name]);

        return UserPatient::factory()->create([
            'user_id' => $user->id,
            'document' => $document,
            'glucose_range_id' => $range->id,
        ]);
    }

    public function test_user_without_permission_cannot_access_the_endpoint(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->getJson('/patients/search?q=maria');

        $response->assertForbidden();
    }

    public function test_it_finds_patients_by_partial_name_match(): void
    {
        $this->actingAsAdministrator();
        $this->createPatient('María Soto', '11111111');
        $this->createPatient('Marcos Peña', '22222222');
        $this->createPatient('Ana Rivas', '33333333');

        $response = $this->getJson('/patients/search?q=mar');

        $response->assertOk();
        $names = collect($response->json('data'))->pluck('name');

        $this->assertTrue($names->contains('María Soto'));
        $this->assertTrue($names->contains('Marcos Peña'));
        $this->assertFalse($names->contains('Ana Rivas'));
    }

    public function test_it_finds_patients_by_rut_with_dots_and_dash(): void
    {
        $this->actingAsAdministrator();
        $this->createPatient('Juan Pérez', '12345678');

        $response = $this->getJson('/patients/search?'.http_build_query(['q' => '12.345.678']));

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.name', 'Juan Pérez');
    }

    public function test_numeric_query_does_not_match_against_name(): void
    {
        $this->actingAsAdministrator();
        $this->createPatient('Patient 123', '99999999');

        $response = $this->getJson('/patients/search?q=123');

        $response->assertOk();
        $response->assertJsonCount(0, 'data');
    }

    public function test_query_below_minimum_length_returns_empty_without_error(): void
    {
        $this->actingAsAdministrator();
        $this->createPatient('Ana', '44444444');

        $response = $this->getJson('/patients/search?q=a');

        $response->assertOk();
        $response->assertJsonCount(0, 'data');
    }

    public function test_no_matches_returns_empty_list(): void
    {
        $this->actingAsAdministrator();
        $this->createPatient('Ana Rivas', '55555555');

        $response = $this->getJson('/patients/search?q=zzz');

        $response->assertOk();
        $response->assertJsonCount(0, 'data');
    }
}
