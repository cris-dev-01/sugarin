<?php

declare(strict_types=1);

namespace Tests\Feature\GlucoseLogs;

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

class PatientGlucoseLogsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([PermissionsSeeder::class, RolesSeeder::class, RolesPermissionsSeeder::class, StatusesSeeder::class]);
        $this->travelTo(Carbon::create(2026, 5, 1, 12, 0, 0));
    }

    private function createPatientWithUser(): UserPatient
    {
        $range = GlucoseRange::factory()->create();
        $user = User::factory()->create();
        $user->assignRole('Patient');

        return UserPatient::factory()->create([
            'user_id' => $user->id,
            'glucose_range_id' => $range->id,
        ]);
    }

    private function createLogAt(UserPatient $patient, Carbon $at): UserGlucoseLog
    {
        $status = Status::where('name', 'Rango normal')->firstOrFail();

        $log = UserGlucoseLog::factory()->create([
            'user_patient_id' => $patient->id,
            'status_id' => $status->id,
            'time_block' => TimeBlock::FASTING->value,
        ]);

        $log->forceFill(['created_at' => $at, 'updated_at' => $at])->save();

        return $log;
    }

    public function test_guest_cannot_access_the_endpoint(): void
    {
        $patient = $this->createPatientWithUser();

        $response = $this->getJson("/patients/{$patient->id}/glucose-logs");

        $response->assertUnauthorized();
    }

    public function test_user_without_permission_cannot_access_the_endpoint(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $patient = $this->createPatientWithUser();

        $response = $this->getJson("/patients/{$patient->id}/glucose-logs");

        $response->assertForbidden();
    }

    public function test_patient_cannot_view_another_patients_logs(): void
    {
        $ownPatient = $this->createPatientWithUser();
        $otherPatient = $this->createPatientWithUser();
        $this->createLogAt($otherPatient, now()->subHour());

        $this->actingAs($ownPatient->user);

        $response = $this->getJson("/patients/{$otherPatient->id}/glucose-logs");

        $response->assertForbidden();
    }

    public function test_patient_can_view_its_own_logs(): void
    {
        $patient = $this->createPatientWithUser();
        $this->createLogAt($patient, now()->subHour());

        $this->actingAs($patient->user);

        $response = $this->getJson("/patients/{$patient->id}/glucose-logs");

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
    }

    public function test_administrator_can_view_any_patients_logs(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('Administrator');
        $this->actingAs($admin);

        $patient = $this->createPatientWithUser();
        $this->createLogAt($patient, now()->subHour());

        $response = $this->getJson("/patients/{$patient->id}/glucose-logs");

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
    }

    public function test_it_paginates_the_results(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('Administrator');
        $this->actingAs($admin);

        $patient = $this->createPatientWithUser();
        for ($i = 0; $i < 20; $i++) {
            $this->createLogAt($patient, now()->subHours($i + 1));
        }

        $firstPage = $this->getJson("/patients/{$patient->id}/glucose-logs?page=1");
        $firstPage->assertOk();
        $firstPage->assertJsonCount(15, 'data');
        $firstPage->assertJsonPath('meta.total', 20);
        $firstPage->assertJsonPath('meta.last_page', 2);

        $secondPage = $this->getJson("/patients/{$patient->id}/glucose-logs?page=2");
        $secondPage->assertJsonCount(5, 'data');
    }

    public function test_it_filters_by_date(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('Administrator');
        $this->actingAs($admin);

        $patient = $this->createPatientWithUser();
        $this->createLogAt($patient, Carbon::create(2026, 4, 20, 9, 0, 0));
        $this->createLogAt($patient, Carbon::create(2026, 4, 21, 9, 0, 0));

        $response = $this->getJson("/patients/{$patient->id}/glucose-logs?date=2026-04-20");

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
    }

    public function test_it_rejects_an_invalid_date_format(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('Administrator');
        $this->actingAs($admin);

        $patient = $this->createPatientWithUser();

        $response = $this->getJson("/patients/{$patient->id}/glucose-logs?date=20-04-2026");

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('date');
    }
}
