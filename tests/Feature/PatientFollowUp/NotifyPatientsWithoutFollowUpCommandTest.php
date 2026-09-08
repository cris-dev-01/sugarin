<?php

declare(strict_types=1);

namespace Tests\Feature\PatientFollowUp;

use App\Enums\NotificationType;
use App\Models\GlucoseRange;
use App\Models\User;
use App\Models\UserGlucoseLog;
use App\Models\UserPatient;
use App\Notifications\AdminFollowUpAlertNotification;
use Carbon\Carbon;
use Database\Seeders\PermissionsSeeder;
use Database\Seeders\RolesPermissionsSeeder;
use Database\Seeders\RolesSeeder;
use Database\Seeders\StatusesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NotifyPatientsWithoutFollowUpCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([PermissionsSeeder::class, RolesSeeder::class, RolesPermissionsSeeder::class, StatusesSeeder::class]);
        $this->travelTo(Carbon::create(2026, 1, 10, 8, 0, 0));
    }

    private function createAdministrator(): User
    {
        $admin = User::factory()->create();
        $admin->assignRole('Administrator');

        return $admin;
    }

    private function createPatient(): UserPatient
    {
        $range = GlucoseRange::factory()->create();

        return UserPatient::factory()->create(['glucose_range_id' => $range->id]);
    }

    public function test_it_notifies_administrators_about_a_patient_without_any_glucose_log(): void
    {
        Notification::fake();
        $admin = $this->createAdministrator();
        $patient = $this->createPatient();

        $this->artisan('notifications:patient-follow-up')->assertSuccessful();

        Notification::assertSentTo($admin, AdminFollowUpAlertNotification::class);
        Notification::assertNotSentTo($patient->user, AdminFollowUpAlertNotification::class);
    }

    public function test_it_notifies_administrators_about_a_patient_with_a_stale_glucose_log(): void
    {
        Notification::fake();
        $admin = $this->createAdministrator();
        $patient = $this->createPatient();

        UserGlucoseLog::factory()->create([
            'user_patient_id' => $patient->id,
            'created_at' => now()->subDays(5),
        ]);

        $this->artisan('notifications:patient-follow-up')->assertSuccessful();

        Notification::assertSentTo($admin, AdminFollowUpAlertNotification::class);
    }

    public function test_it_does_not_notify_about_a_patient_with_a_recent_glucose_log(): void
    {
        Notification::fake();
        $admin = $this->createAdministrator();
        $patient = $this->createPatient();

        UserGlucoseLog::factory()->create([
            'user_patient_id' => $patient->id,
            'created_at' => now()->subDay(),
        ]);

        $this->artisan('notifications:patient-follow-up')->assertSuccessful();

        Notification::assertNotSentTo($admin, AdminFollowUpAlertNotification::class);
    }

    public function test_it_never_notifies_the_patient_directly(): void
    {
        Notification::fake();
        $this->createAdministrator();
        $patient = $this->createPatient();

        $this->artisan('notifications:patient-follow-up')->assertSuccessful();

        Notification::assertNothingSentTo($patient->user);
    }

    public function test_it_notifies_administrators_through_mail_and_database(): void
    {
        Notification::fake();
        $admin = $this->createAdministrator();
        $this->createPatient();

        $this->artisan('notifications:patient-follow-up')->assertSuccessful();

        Notification::assertSentTo($admin, AdminFollowUpAlertNotification::class, function ($notification, $channels) {
            return $channels === ['database', 'mail'];
        });
    }

    public function test_it_does_not_report_the_same_patient_twice_the_same_day(): void
    {
        $this->createAdministrator();
        $patient = $this->createPatient();

        $this->artisan('notifications:patient-follow-up')->assertSuccessful();
        $this->artisan('notifications:patient-follow-up')->assertSuccessful();

        $this->assertSame(
            1,
            DB::table('notifications')
                ->where('type', NotificationType::OverdueGlucoseLog->value)
                ->where('data->patient_id', $patient->id)
                ->count()
        );
    }
}
