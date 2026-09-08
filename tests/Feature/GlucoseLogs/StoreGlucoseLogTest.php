<?php

declare(strict_types=1);

namespace Tests\Feature\GlucoseLogs;

use App\Models\GlucoseRange;
use App\Models\Status;
use App\Models\User;
use App\Models\UserPatient;
use App\Notifications\GlucoseLogRegisteredNotification;
use Carbon\Carbon;
use Database\Seeders\PermissionsSeeder;
use Database\Seeders\RolesPermissionsSeeder;
use Database\Seeders\RolesSeeder;
use Database\Seeders\StatusesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class StoreGlucoseLogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([PermissionsSeeder::class, RolesSeeder::class, RolesPermissionsSeeder::class, StatusesSeeder::class]);
        $this->travelTo(Carbon::create(2026, 1, 1, 10, 0, 0));
    }

    private function actingAsAdministrator(): User
    {
        $admin = User::factory()->create();
        $admin->assignRole('Administrator');

        $this->actingAs($admin);

        return $admin;
    }

    private function createPatient(array $rangeOverrides = []): UserPatient
    {
        $range = GlucoseRange::factory()->create($rangeOverrides);

        return UserPatient::factory()->create(['glucose_range_id' => $range->id]);
    }

    public function test_guest_cannot_store_a_glucose_log(): void
    {
        $patient = $this->createPatient();

        $response = $this->postJson('/glucose-logs', [
            'user_patient_id' => $patient->id,
            'value' => 90,
        ]);

        $response->assertUnauthorized();
    }

    public function test_user_without_permission_cannot_store_a_glucose_log(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $patient = $this->createPatient();

        $response = $this->postJson('/glucose-logs', [
            'user_patient_id' => $patient->id,
            'value' => 90,
        ]);

        $response->assertForbidden();
    }

    public function test_it_stores_a_glucose_log_within_the_normal_range(): void
    {
        $this->actingAsAdministrator();

        $patient = $this->createPatient([
            'min_fasting_value' => 70,
            'max_fasting_value' => 100,
        ]);

        $response = $this->postJson('/glucose-logs', [
            'user_patient_id' => $patient->id,
            'value' => 90,
        ]);

        $response->assertCreated();
        $response->assertJsonPath('data.value', 90);
        $response->assertJsonPath('data.time_block', 'mañana');
        $response->assertJsonPath('data.status.name', 'Rango normal');
        $response->assertJsonPath('data.is_abnormal', false);
        $response->assertJsonPath('data.range.min', 70);
        $response->assertJsonPath('data.range.max', 100);

        $this->assertDatabaseHas('user_glucose_logs', [
            'user_patient_id' => $patient->id,
            'value' => 90,
            'time_block' => 'mañana',
            'status_id' => Status::where('name', 'Rango normal')->firstOrFail()->id,
        ]);
    }

    public function test_it_stores_a_glucose_log_above_the_range_as_elevated(): void
    {
        $this->actingAsAdministrator();

        $patient = $this->createPatient([
            'min_fasting_value' => 70,
            'max_fasting_value' => 100,
        ]);

        $response = $this->postJson('/glucose-logs', [
            'user_patient_id' => $patient->id,
            'value' => 180,
        ]);

        $response->assertCreated();
        $response->assertJsonPath('data.status.name', 'Elevado - fuera de rango normal');
        $response->assertJsonPath('data.is_abnormal', true);
    }

    public function test_it_stores_a_glucose_log_below_the_range_as_low(): void
    {
        $this->actingAsAdministrator();

        $patient = $this->createPatient([
            'min_fasting_value' => 70,
            'max_fasting_value' => 100,
        ]);

        $response = $this->postJson('/glucose-logs', [
            'user_patient_id' => $patient->id,
            'value' => 50,
        ]);

        $response->assertCreated();
        $response->assertJsonPath('data.status.name', 'Bajo - fuera de rango normal');
        $response->assertJsonPath('data.is_abnormal', true);
    }

    public function test_it_uses_non_fasting_thresholds_during_the_evening_block(): void
    {
        $this->travelTo(Carbon::create(2026, 1, 1, 21, 0, 0));
        $this->actingAsAdministrator();

        $patient = $this->createPatient([
            'min_non_fasting_value' => 80,
            'max_non_fasting_value' => 140,
        ]);

        $response = $this->postJson('/glucose-logs', [
            'user_patient_id' => $patient->id,
            'value' => 150,
        ]);

        $response->assertCreated();
        $response->assertJsonPath('data.time_block', 'anochecer');
        $response->assertJsonPath('data.status.name', 'Elevado - fuera de rango normal');
        $response->assertJsonPath('data.range.min', 80);
        $response->assertJsonPath('data.range.max', 140);
    }

    public function test_it_rejects_an_invalid_value(): void
    {
        $this->actingAsAdministrator();

        $patient = $this->createPatient();

        $response = $this->postJson('/glucose-logs', [
            'user_patient_id' => $patient->id,
            'value' => 0,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('value');
    }

    public function test_it_rejects_a_nonexistent_patient(): void
    {
        $this->actingAsAdministrator();

        $response = $this->postJson('/glucose-logs', [
            'user_patient_id' => 999_999,
            'value' => 90,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('user_patient_id');
    }

    public function test_it_rejects_a_patient_without_a_glucose_range_assigned(): void
    {
        $this->actingAsAdministrator();

        $patient = $this->createPatient();
        $patient->glucoseRange->delete();

        $response = $this->postJson('/glucose-logs', [
            'user_patient_id' => $patient->id,
            'value' => 90,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('user_patient_id');
    }

    public function test_it_notifies_the_patient_for_a_reading_within_the_normal_range(): void
    {
        Notification::fake();
        $this->actingAsAdministrator();

        $patient = $this->createPatient([
            'min_fasting_value' => 70,
            'max_fasting_value' => 100,
        ]);

        $this->postJson('/glucose-logs', [
            'user_patient_id' => $patient->id,
            'value' => 90,
        ])->assertCreated();

        Notification::assertSentTo($patient->user, GlucoseLogRegisteredNotification::class, function ($notification, $channels) {
            return $channels === ['database', 'mail'];
        });
    }

    public function test_it_notifies_the_patient_for_a_reading_out_of_range(): void
    {
        Notification::fake();
        $this->actingAsAdministrator();

        $patient = $this->createPatient([
            'min_fasting_value' => 70,
            'max_fasting_value' => 100,
        ]);

        $this->postJson('/glucose-logs', [
            'user_patient_id' => $patient->id,
            'value' => 180,
        ])->assertCreated();

        Notification::assertSentTo($patient->user, GlucoseLogRegisteredNotification::class);
    }

    public function test_it_does_not_notify_when_the_log_fails_to_register(): void
    {
        Notification::fake();
        $this->actingAsAdministrator();

        $patient = $this->createPatient();
        $patient->glucoseRange->delete();

        $this->postJson('/glucose-logs', [
            'user_patient_id' => $patient->id,
            'value' => 90,
        ])->assertUnprocessable();

        Notification::assertNothingSent();
    }
}
