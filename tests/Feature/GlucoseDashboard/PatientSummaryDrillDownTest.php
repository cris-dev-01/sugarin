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
use Database\Seeders\SettingsSeeder;
use Database\Seeders\StatusesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientSummaryDrillDownTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([PermissionsSeeder::class, RolesSeeder::class, RolesPermissionsSeeder::class, StatusesSeeder::class, SettingsSeeder::class]);
        $this->travelTo(Carbon::create(2026, 2, 1, 10, 0, 0));
    }

    private function actingAsAdministrator(): User
    {
        $admin = User::factory()->create();
        $admin->assignRole('Administrator');
        $this->actingAs($admin);

        return $admin;
    }

    private function createPatient(): UserPatient
    {
        $range = GlucoseRange::factory()->create();

        return UserPatient::factory()->create(['glucose_range_id' => $range->id]);
    }

    private function createLog(UserPatient $patient, string $statusName, TimeBlock $timeBlock, Carbon $at): UserGlucoseLog
    {
        $status = Status::where('name', $statusName)->firstOrFail();

        $log = UserGlucoseLog::factory()->create([
            'user_patient_id' => $patient->id,
            'status_id' => $status->id,
            'time_block' => $timeBlock->value,
        ]);

        $log->forceFill(['created_at' => $at, 'updated_at' => $at])->save();

        return $log;
    }

    public function test_user_without_permission_cannot_access_drill_down(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $patient = $this->createPatient();

        $response = $this->getJson("/patients/{$patient->id}/summary");

        $response->assertForbidden();
    }

    public function test_it_rejects_an_invalid_period(): void
    {
        $this->actingAsAdministrator();
        $patient = $this->createPatient();

        $response = $this->getJson("/patients/{$patient->id}/summary?period=15");

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('period');
    }

    public function test_it_returns_zeroed_summary_when_patient_has_no_logs_in_period(): void
    {
        $this->actingAsAdministrator();
        $patient = $this->createPatient();

        $response = $this->getJson("/patients/{$patient->id}/summary?period=30");

        $response->assertOk();
        $response->assertJsonPath('data.in_range_percentage.combined', null);
        $response->assertJsonPath('data.last_reading', null);
        $response->assertJsonPath('data.adherence_percentage', 0);
        $response->assertJsonPath('data.recent_logs', []);
    }

    public function test_it_returns_only_the_20_most_recent_logs_newest_first(): void
    {
        $this->actingAsAdministrator();
        $patient = $this->createPatient();

        for ($i = 0; $i < 25; $i++) {
            $this->createLog($patient, 'Rango normal', TimeBlock::FASTING, now()->subHours($i + 1));
        }

        $response = $this->getJson("/patients/{$patient->id}/summary?period=30");

        $response->assertOk();
        $recentLogs = $response->json('data.recent_logs');

        $this->assertCount(20, $recentLogs);
        $this->assertTrue(
            Carbon::parse($recentLogs[0]['created_at'])->greaterThan(Carbon::parse($recentLogs[1]['created_at']))
        );
    }

    public function test_it_calculates_in_range_percentage_separately_per_time_block(): void
    {
        $this->actingAsAdministrator();
        $patient = $this->createPatient();

        for ($i = 0; $i < 8; $i++) {
            $this->createLog($patient, 'Rango normal', TimeBlock::FASTING, now()->subHours($i + 1));
        }
        for ($i = 8; $i < 10; $i++) {
            $this->createLog($patient, 'Elevado - fuera de rango normal', TimeBlock::FASTING, now()->subHours($i + 1));
        }
        for ($i = 0; $i < 3; $i++) {
            $this->createLog($patient, 'Rango normal', TimeBlock::NON_FASTING, now()->subHours($i + 20));
        }
        for ($i = 3; $i < 5; $i++) {
            $this->createLog($patient, 'Bajo - fuera de rango normal', TimeBlock::NON_FASTING, now()->subHours($i + 20));
        }

        $response = $this->getJson("/patients/{$patient->id}/summary?period=30");

        $response->assertOk();
        $response->assertJsonPath('data.in_range_percentage.mañana', 80);
        $response->assertJsonPath('data.in_range_percentage.anochecer', 60);
        $response->assertJsonPath('data.in_range_percentage.combined', 73.3);
    }

    public function test_it_counts_low_and_elevated_events(): void
    {
        $this->actingAsAdministrator();
        $patient = $this->createPatient();

        $this->createLog($patient, 'Bajo - fuera de rango normal', TimeBlock::FASTING, now()->subHours(1));
        $this->createLog($patient, 'Bajo - fuera de rango normal', TimeBlock::FASTING, now()->subHours(2));
        $this->createLog($patient, 'Elevado - fuera de rango normal', TimeBlock::NON_FASTING, now()->subHours(3));

        $response = $this->getJson("/patients/{$patient->id}/summary?period=30");

        $response->assertJsonPath('data.events.bajo.total', 2);
        $response->assertJsonPath('data.events.elevado.total', 1);
    }

    public function test_it_calculates_partial_adherence(): void
    {
        $this->actingAsAdministrator();
        $patient = $this->createPatient();

        for ($i = 0; $i < 15; $i++) {
            $this->createLog($patient, 'Rango normal', TimeBlock::FASTING, now()->subHours($i + 1));
        }

        $response = $this->getJson("/patients/{$patient->id}/summary?period=30");

        $response->assertJsonPath('data.adherence_percentage', 25);
    }

    public function test_it_returns_status_distribution_with_all_three_states(): void
    {
        $this->actingAsAdministrator();
        $patient = $this->createPatient();

        for ($i = 0; $i < 10; $i++) {
            $this->createLog($patient, 'Rango normal', TimeBlock::FASTING, now()->subHours($i + 1));
        }
        for ($i = 0; $i < 3; $i++) {
            $this->createLog($patient, 'Elevado - fuera de rango normal', TimeBlock::FASTING, now()->subHours($i + 20));
        }
        for ($i = 0; $i < 2; $i++) {
            $this->createLog($patient, 'Bajo - fuera de rango normal', TimeBlock::FASTING, now()->subHours($i + 30));
        }

        $response = $this->getJson("/patients/{$patient->id}/summary?period=30");

        $response->assertOk();
        $distribution = collect($response->json('data.status_distribution'))->keyBy('status');

        $this->assertEquals(10, $distribution['Rango normal']['count']);
        $this->assertEquals(66.7, $distribution['Rango normal']['percentage']);
        $this->assertEquals(3, $distribution['Elevado - fuera de rango normal']['count']);
        $this->assertEquals(20.0, $distribution['Elevado - fuera de rango normal']['percentage']);
        $this->assertEquals(2, $distribution['Bajo - fuera de rango normal']['count']);
        $this->assertEquals(13.3, $distribution['Bajo - fuera de rango normal']['percentage']);
    }
}
