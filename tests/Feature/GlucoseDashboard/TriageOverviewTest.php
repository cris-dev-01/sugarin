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
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class TriageOverviewTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([PermissionsSeeder::class, RolesSeeder::class, RolesPermissionsSeeder::class, StatusesSeeder::class, SettingsSeeder::class]);
        $this->travelTo(Carbon::create(2026, 1, 15, 12, 0, 0));
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

    private function createLogAt(UserPatient $patient, string $statusName, Carbon $at): UserGlucoseLog
    {
        $status = Status::where('name', $statusName)->firstOrFail();

        $log = UserGlucoseLog::factory()->create([
            'user_patient_id' => $patient->id,
            'status_id' => $status->id,
            'time_block' => TimeBlock::FASTING->value,
        ]);

        $log->forceFill(['created_at' => $at, 'updated_at' => $at])->save();

        return $log;
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/');

        $response->assertRedirect(route('login'));
    }

    public function test_user_without_permission_cannot_view_the_dashboard(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/');

        $response->assertForbidden();
    }

    public function test_it_renders_zeroed_triage_when_there_are_no_patients(): void
    {
        $this->actingAsAdministrator();

        $response = $this->get('/');

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard/Index')
            ->where('triage.low_recent_count', 0)
            ->where('triage.inactive_count', 0)
            ->where('triage.good_control_percentage', 0)
            ->where('triage.patients', [])
        );
    }

    public function test_patient_with_low_event_within_window_counts_as_low_recent(): void
    {
        $this->actingAsAdministrator();
        $patient = $this->createPatient();
        $this->createLogAt($patient, 'Bajo - fuera de rango normal', now()->subHours(10));

        $response = $this->get('/');

        $response->assertInertia(fn (Assert $page) => $page->where('triage.low_recent_count', 1));
    }

    public function test_patient_with_low_event_outside_window_does_not_count_as_low_recent(): void
    {
        $this->actingAsAdministrator();
        $patient = $this->createPatient();
        $this->createLogAt($patient, 'Bajo - fuera de rango normal', now()->subHours(72));

        $response = $this->get('/');

        $response->assertInertia(fn (Assert $page) => $page->where('triage.low_recent_count', 0));
    }

    public function test_patient_without_recent_log_counts_as_inactive(): void
    {
        $this->actingAsAdministrator();
        $patient = $this->createPatient();
        $this->createLogAt($patient, 'Rango normal', now()->subHours(60));

        $response = $this->get('/');

        $response->assertInertia(fn (Assert $page) => $page->where('triage.inactive_count', 1));
    }

    public function test_patient_without_any_log_counts_as_inactive(): void
    {
        $this->actingAsAdministrator();
        $this->createPatient();

        $response = $this->get('/');

        $response->assertInertia(fn (Assert $page) => $page->where('triage.inactive_count', 1));
    }

    public function test_good_control_percentage_counts_patients_at_or_above_threshold(): void
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

        $response = $this->get('/');

        $response->assertInertia(fn (Assert $page) => $page->where('triage.good_control_percentage', 50));
    }

    public function test_patient_without_records_in_reference_period_is_excluded_from_good_control(): void
    {
        $this->actingAsAdministrator();
        $this->createPatient();

        $response = $this->get('/');

        $response->assertInertia(fn (Assert $page) => $page->where('triage.good_control_percentage', 0));
    }

    public function test_patient_with_low_recent_event_is_ranked_first(): void
    {
        $this->actingAsAdministrator();

        $riskyPatient = $this->createPatient('Riesgo');
        $this->createLogAt($riskyPatient, 'Bajo - fuera de rango normal', now()->subHours(5));

        $stablePatient = $this->createPatient('Estable');
        $this->createLogAt($stablePatient, 'Elevado - fuera de rango normal', now()->subHours(1));

        $response = $this->get('/');

        $response->assertInertia(fn (Assert $page) => $page->where('triage.patients.0.name', 'Riesgo'));
    }

    public function test_ties_on_low_recent_are_broken_by_inactivity(): void
    {
        $this->actingAsAdministrator();

        $moreInactive = $this->createPatient('Mas Inactivo');
        $this->createLogAt($moreInactive, 'Rango normal', now()->subHours(40));

        $lessInactive = $this->createPatient('Menos Inactivo');
        $this->createLogAt($lessInactive, 'Rango normal', now()->subHours(5));

        $response = $this->get('/');

        $response->assertInertia(fn (Assert $page) => $page->where('triage.patients.0.name', 'Mas Inactivo'));
    }

    public function test_patient_query_param_preloads_that_patients_summary(): void
    {
        $this->actingAsAdministrator();

        $riskyPatient = $this->createPatient('Riesgo');
        $this->createLogAt($riskyPatient, 'Bajo - fuera de rango normal', now()->subHours(5));

        $otherPatient = $this->createPatient('Otro');
        $this->createLogAt($otherPatient, 'Rango normal', now()->subHours(1));

        $response = $this->get('/?patient='.$otherPatient->id);

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->where('triage.patients.0.name', 'Riesgo')
            ->where('summary.patient.id', $otherPatient->id)
            ->where('summary.patient.name', 'Otro')
        );
    }

    public function test_invalid_patient_query_param_falls_back_to_default_selection(): void
    {
        $this->actingAsAdministrator();

        $riskyPatient = $this->createPatient('Riesgo');
        $this->createLogAt($riskyPatient, 'Bajo - fuera de rango normal', now()->subHours(5));

        $response = $this->get('/?patient=999999');

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->where('summary.patient.id', $riskyPatient->id)
        );
    }
}
