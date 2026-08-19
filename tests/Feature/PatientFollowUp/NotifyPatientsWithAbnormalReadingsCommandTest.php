<?php

declare(strict_types=1);

namespace Tests\Feature\PatientFollowUp;

use App\Enums\NotificationType;
use App\Models\GlucoseRange;
use App\Models\Status;
use App\Models\User;
use App\Models\UserGlucoseLog;
use App\Models\UserPatient;
use App\Notifications\AdminAbnormalReadingAlertNotification;
use Carbon\Carbon;
use Database\Seeders\PermissionsSeeder;
use Database\Seeders\RolesPermissionsSeeder;
use Database\Seeders\RolesSeeder;
use Database\Seeders\StatusesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NotifyPatientsWithAbnormalReadingsCommandTest extends TestCase
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

    private function createLog(UserPatient $patient, string $statusName, \DateTimeInterface $createdAt): UserGlucoseLog
    {
        return UserGlucoseLog::factory()->create([
            'user_patient_id' => $patient->id,
            'status_id' => Status::where('name', $statusName)->firstOrFail()->id,
            'created_at' => $createdAt,
        ]);
    }

    public function test_it_notifies_administrators_about_a_patient_with_a_recent_high_reading(): void
    {
        Notification::fake();
        $admin = $this->createAdministrator();
        $patient = $this->createPatient();

        $this->createLog($patient, 'Elevado - fuera de rango normal', now()->subHours(2));

        $this->artisan('notifications:patient-abnormal-readings')->assertSuccessful();

        Notification::assertSentTo($admin, AdminAbnormalReadingAlertNotification::class);
        Notification::assertNotSentTo($patient->user, AdminAbnormalReadingAlertNotification::class);
    }

    public function test_it_notifies_administrators_about_a_patient_with_a_recent_low_reading(): void
    {
        Notification::fake();
        $admin = $this->createAdministrator();
        $patient = $this->createPatient();

        $this->createLog($patient, 'Bajo - fuera de rango normal', now()->subHours(2));

        $this->artisan('notifications:patient-abnormal-readings')->assertSuccessful();

        Notification::assertSentTo($admin, AdminAbnormalReadingAlertNotification::class);
    }

    public function test_it_notifies_administrators_only_once_for_a_patient_with_multiple_abnormal_readings(): void
    {
        Notification::fake();
        $admin = $this->createAdministrator();
        $patient = $this->createPatient();

        $this->createLog($patient, 'Elevado - fuera de rango normal', now()->subHours(5));
        $this->createLog($patient, 'Bajo - fuera de rango normal', now()->subHours(1));

        $this->artisan('notifications:patient-abnormal-readings')->assertSuccessful();

        Notification::assertSentToTimes($admin, AdminAbnormalReadingAlertNotification::class, 1);
    }

    public function test_it_does_not_notify_about_a_patient_without_abnormal_readings(): void
    {
        Notification::fake();
        $admin = $this->createAdministrator();
        $patient = $this->createPatient();

        $this->createLog($patient, 'Rango normal', now()->subHours(2));

        $this->artisan('notifications:patient-abnormal-readings')->assertSuccessful();

        Notification::assertNotSentTo($admin, AdminAbnormalReadingAlertNotification::class);
    }

    public function test_it_does_not_notify_about_a_patient_with_an_abnormal_reading_outside_the_window(): void
    {
        Notification::fake();
        $admin = $this->createAdministrator();
        $patient = $this->createPatient();

        $this->createLog($patient, 'Elevado - fuera de rango normal', now()->subHours(30));

        $this->artisan('notifications:patient-abnormal-readings')->assertSuccessful();

        Notification::assertNotSentTo($admin, AdminAbnormalReadingAlertNotification::class);
    }

    public function test_it_never_notifies_the_patient_directly(): void
    {
        Notification::fake();
        $this->createAdministrator();
        $patient = $this->createPatient();

        $this->createLog($patient, 'Elevado - fuera de rango normal', now()->subHours(2));

        $this->artisan('notifications:patient-abnormal-readings')->assertSuccessful();

        Notification::assertNothingSentTo($patient->user);
    }

    public function test_it_notifies_administrators_through_mail_and_database(): void
    {
        Notification::fake();
        $admin = $this->createAdministrator();
        $patient = $this->createPatient();

        $this->createLog($patient, 'Elevado - fuera de rango normal', now()->subHours(2));

        $this->artisan('notifications:patient-abnormal-readings')->assertSuccessful();

        Notification::assertSentTo($admin, AdminAbnormalReadingAlertNotification::class, function ($notification, $channels) {
            return $channels === ['database', 'mail'];
        });
    }

    public function test_it_does_not_report_the_same_patient_twice_the_same_day(): void
    {
        $this->createAdministrator();
        $patient = $this->createPatient();

        $this->createLog($patient, 'Elevado - fuera de rango normal', now()->subHours(2));

        $this->artisan('notifications:patient-abnormal-readings')->assertSuccessful();
        $this->artisan('notifications:patient-abnormal-readings')->assertSuccessful();

        $this->assertSame(
            1,
            DB::table('notifications')
                ->where('type', NotificationType::AbnormalGlucoseLog->value)
                ->where('data->patient_id', $patient->id)
                ->count()
        );
    }
}
