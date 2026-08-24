<?php

declare(strict_types=1);

namespace Tests\Feature\Settings;

use App\Actions\Settings\GetDashboardSettingsSrv;
use App\Models\GlucoseRange;
use App\Models\Setting;
use App\Models\Status;
use App\Models\User;
use App\Models\UserGlucoseLog;
use App\Models\UserPatient;
use Database\Seeders\PermissionsSeeder;
use Database\Seeders\RolesPermissionsSeeder;
use Database\Seeders\RolesSeeder;
use Database\Seeders\SettingsSeeder;
use Database\Seeders\StatusesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DashboardSettingsTest extends TestCase
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

    public function test_get_dashboard_settings_srv_returns_seeded_values(): void
    {
        $settings = GetDashboardSettingsSrv::run();

        $this->assertSame(48, $settings->recent_event_window_hours);
        $this->assertSame(0.8, $settings->good_control_threshold);
        $this->assertSame(30, $settings->good_control_reference_period_days);
        $this->assertSame(2, $settings->expected_logs_per_day);
    }

    public function test_get_dashboard_settings_srv_caches_after_first_read(): void
    {
        GetDashboardSettingsSrv::run();

        DB::enableQueryLog();
        GetDashboardSettingsSrv::run();

        $this->assertEmpty(DB::getQueryLog());
    }

    public function test_administrator_can_view_the_settings_page(): void
    {
        $this->actingAsAdministrator();

        $response = $this->get('/dashboard-settings');

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('DashboardSettings/Index')
            ->where('settings.recent_event_window_hours', 48)
        );
    }

    public function test_user_without_permission_cannot_view_the_settings_page(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/dashboard-settings');

        $response->assertForbidden();
    }

    public function test_administrator_can_update_the_settings(): void
    {
        $this->actingAsAdministrator();

        $response = $this->put('/dashboard-settings', [
            'recent_event_window_hours' => 72,
            'good_control_threshold' => 0.85,
            'good_control_reference_period_days' => 30,
            'expected_logs_per_day' => 3,
        ]);

        $response->assertRedirect(route('dashboard-settings.index'));
        $this->assertSame('72', Setting::query()->where('key', 'recent_event_window_hours')->value('value'));
        $this->assertSame('3', Setting::query()->where('key', 'expected_logs_per_day')->value('value'));
    }

    public function test_update_rejects_out_of_range_value_and_persists_nothing(): void
    {
        $this->actingAsAdministrator();

        $response = $this->put('/dashboard-settings', [
            'recent_event_window_hours' => 48,
            'good_control_threshold' => 1.5,
            'good_control_reference_period_days' => 30,
            'expected_logs_per_day' => 2,
        ]);

        $response->assertSessionHasErrors('good_control_threshold');
        $this->assertSame('0.8', Setting::query()->where('key', 'good_control_threshold')->value('value'));
    }

    public function test_user_without_permission_cannot_update_the_settings(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->put('/dashboard-settings', [
            'recent_event_window_hours' => 72,
            'good_control_threshold' => 0.85,
            'good_control_reference_period_days' => 30,
            'expected_logs_per_day' => 3,
        ]);

        $response->assertForbidden();
        $this->assertSame('48', Setting::query()->where('key', 'recent_event_window_hours')->value('value'));
    }

    public function test_updating_a_setting_invalidates_the_cache_for_subsequent_reads(): void
    {
        $this->actingAsAdministrator();

        $this->assertSame(48, GetDashboardSettingsSrv::run()->recent_event_window_hours);

        $this->put('/dashboard-settings', [
            'recent_event_window_hours' => 24,
            'good_control_threshold' => 0.8,
            'good_control_reference_period_days' => 30,
            'expected_logs_per_day' => 2,
        ]);

        $this->assertSame(24, GetDashboardSettingsSrv::run()->recent_event_window_hours);
    }

    public function test_changing_the_window_setting_affects_triage_overview_without_a_deploy(): void
    {
        $admin = $this->actingAsAdministrator();

        $range = GlucoseRange::factory()->create();
        $patientUser = User::factory()->create();
        $patient = UserPatient::factory()->create([
            'user_id' => $patientUser->id,
            'glucose_range_id' => $range->id,
        ]);
        $status = Status::where('name', 'Bajo - fuera de rango normal')->firstOrFail();

        $log = UserGlucoseLog::factory()->create([
            'user_patient_id' => $patient->id,
            'status_id' => $status->id,
        ]);
        $loggedAt = now()->subHours(30);
        $log->forceFill(['created_at' => $loggedAt, 'updated_at' => $loggedAt])->save();

        $this->actingAs($admin);
        $before = $this->get('/');
        $before->assertInertia(fn (Assert $page) => $page->where('triage.low_recent_count', 1));

        $this->put('/dashboard-settings', [
            'recent_event_window_hours' => 24,
            'good_control_threshold' => 0.8,
            'good_control_reference_period_days' => 30,
            'expected_logs_per_day' => 2,
        ]);

        $after = $this->get('/');
        $after->assertInertia(fn (Assert $page) => $page->where('triage.low_recent_count', 0));
    }
}
