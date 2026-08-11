<?php

declare(strict_types=1);

namespace Tests\Feature\GlucoseDashboard;

use App\Enums\TimeBlock;
use App\Models\GlucoseRange;
use App\Models\Status;
use App\Models\User;
use App\Models\UserGlucoseLog;
use App\Models\UserPatient;
use Carbon\Carbon;
use Database\Seeders\PermissionsSeeder;
use Database\Seeders\RolesPermissionsSeeder;
use Database\Seeders\RolesSeeder;
use Database\Seeders\StatusesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TriagePatientsByCriteriaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([PermissionsSeeder::class, RolesSeeder::class, RolesPermissionsSeeder::class, StatusesSeeder::class]);
        $this->travelTo(Carbon::create(2026, 4, 1, 12, 0, 0));
    }

    private function actingAsAdministrator(): User
    {
        $admin = User::factory()->create();
        $admin->assignRole('Administrator');
        $this->actingAs($admin);

        return $admin;
    }

    private function createPatient(string $name = 'Paciente'): UserPatient
    {
        $range = GlucoseRange::factory()->create();
        $user = User::factory()->create(['name' => $name]);

        return UserPatient::factory()->create([
            'user_id' => $user->id,
            'glucose_range_id' => $range->id,
        ]);
    }

    private function createLogAt(UserPatient $patient, string $statusName, Carbon $at, int $value = 90): UserGlucoseLog
    {
        $status = Status::where('name', $statusName)->firstOrFail();

        $log = UserGlucoseLog::factory()->create([
            'user_patient_id' => $patient->id,
            'status_id' => $status->id,
            'value' => $value,
            'time_block' => TimeBlock::FASTING->value,
        ]);

        $log->forceFill(['created_at' => $at, 'updated_at' => $at])->save();

        return $log;
    }

    public function test_user_without_permission_cannot_access_the_endpoint(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->getJson('/dashboard/triage-patients?criteria=low_recent');

        $response->assertForbidden();
    }

    public function test_it_rejects_an_invalid_criteria(): void
    {
        $this->actingAsAdministrator();

        $response = $this->getJson('/dashboard/triage-patients?criteria=unknown');

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('criteria');
    }

    public function test_it_rejects_a_missing_criteria(): void
    {
        $this->actingAsAdministrator();

        $response = $this->getJson('/dashboard/triage-patients');

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('criteria');
    }

    public function test_low_recent_returns_only_patients_with_a_low_event_within_the_window(): void
    {
        $this->actingAsAdministrator();

        $withinWindow = $this->createPatient('Reciente');
        $this->createLogAt($withinWindow, 'Bajo - fuera de rango normal', now()->subHours(10), 55);

        $outsideWindow = $this->createPatient('Antiguo');
        $this->createLogAt($outsideWindow, 'Bajo - fuera de rango normal', now()->subHours(72));

        $noEvent = $this->createPatient('Sin Evento');
        $this->createLogAt($noEvent, 'Rango normal', now()->subHours(1));

        $response = $this->getJson('/dashboard/triage-patients?criteria=low_recent');

        $response->assertOk();
        $response->assertJsonPath('data.criteria', 'low_recent');
        $response->assertJsonCount(1, 'data.patients');
        $response->assertJsonPath('data.patients.0.name', 'Reciente');
        $response->assertJsonPath('data.patients.0.last_low_value', 55);
    }

    public function test_low_recent_returns_the_most_recent_low_event_per_patient(): void
    {
        $this->actingAsAdministrator();

        $patient = $this->createPatient();
        $this->createLogAt($patient, 'Bajo - fuera de rango normal', now()->subHours(20), 60);
        $this->createLogAt($patient, 'Bajo - fuera de rango normal', now()->subHours(5), 50);

        $response = $this->getJson('/dashboard/triage-patients?criteria=low_recent');

        $response->assertJsonCount(1, 'data.patients');
        $response->assertJsonPath('data.patients.0.last_low_value', 50);
    }

    public function test_inactive_returns_patients_without_a_recent_log(): void
    {
        $this->actingAsAdministrator();

        $inactivePatient = $this->createPatient('Inactivo');
        $this->createLogAt($inactivePatient, 'Rango normal', now()->subHours(60));

        $neverLogged = $this->createPatient('Nunca');

        $activePatient = $this->createPatient('Activo');
        $this->createLogAt($activePatient, 'Rango normal', now()->subHours(2));

        $response = $this->getJson('/dashboard/triage-patients?criteria=inactive');

        $response->assertOk();
        $names = collect($response->json('data.patients'))->pluck('name');

        $this->assertTrue($names->contains('Inactivo'));
        $this->assertTrue($names->contains('Nunca'));
        $this->assertFalse($names->contains('Activo'));
    }

    public function test_inactive_sorts_the_most_inactive_patients_first(): void
    {
        $this->actingAsAdministrator();

        $moreInactive = $this->createPatient('Mas Inactivo');
        $this->createLogAt($moreInactive, 'Rango normal', now()->subHours(90));

        $lessInactive = $this->createPatient('Menos Inactivo');
        $this->createLogAt($lessInactive, 'Rango normal', now()->subHours(50));

        $response = $this->getJson('/dashboard/triage-patients?criteria=inactive');

        $response->assertJsonPath('data.patients.0.name', 'Mas Inactivo');
        $response->assertJsonPath('data.patients.1.name', 'Menos Inactivo');
    }

    public function test_good_control_returns_only_patients_at_or_above_the_threshold(): void
    {
        $this->actingAsAdministrator();

        $goodPatient = $this->createPatient('Buen Control');
        for ($i = 0; $i < 9; $i++) {
            $this->createLogAt($goodPatient, 'Rango normal', now()->subHours($i + 1));
        }
        $this->createLogAt($goodPatient, 'Elevado - fuera de rango normal', now()->subHours(11));

        $badPatient = $this->createPatient('Mal Control');
        $this->createLogAt($badPatient, 'Bajo - fuera de rango normal', now()->subHours(1));
        $this->createLogAt($badPatient, 'Rango normal', now()->subHours(2));

        $withoutRecords = $this->createPatient('Sin Registros');

        $response = $this->getJson('/dashboard/triage-patients?criteria=good_control');

        $response->assertOk();
        $response->assertJsonCount(1, 'data.patients');
        $response->assertJsonPath('data.patients.0.name', 'Buen Control');
        $response->assertJsonPath('data.patients.0.in_range_percentage', 90);
    }
}
