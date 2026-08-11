<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\GlucoseDashboard\CalculatePatientStreak;
use App\Models\GlucoseRange;
use App\Models\Status;
use App\Models\UserGlucoseLog;
use App\Models\UserPatient;
use Carbon\Carbon;
use Database\Seeders\StatusesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalculatePatientStreakTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(StatusesSeeder::class);
        $this->travelTo(Carbon::create(2026, 3, 10, 15, 0, 0));
    }

    private function createPatient(): UserPatient
    {
        $range = GlucoseRange::factory()->create();

        return UserPatient::factory()->create(['glucose_range_id' => $range->id]);
    }

    private function logOnDay(UserPatient $patient, Carbon $day, string $statusName): void
    {
        $status = Status::where('name', $statusName)->firstOrFail();

        $log = UserGlucoseLog::factory()->create([
            'user_patient_id' => $patient->id,
            'status_id' => $status->id,
        ]);

        $at = $day->copy()->setTime(9, 0);
        $log->forceFill(['created_at' => $at, 'updated_at' => $at])->save();
    }

    public function test_streak_counts_consecutive_normal_days_including_today(): void
    {
        $patient = $this->createPatient();

        for ($i = 0; $i < 5; $i++) {
            $this->logOnDay($patient, today()->subDays($i), 'Rango normal');
        }

        $streak = (new CalculatePatientStreak)->handle($patient->id);

        $this->assertSame(5, $streak);
    }

    public function test_streak_breaks_on_a_day_with_an_out_of_range_reading(): void
    {
        $patient = $this->createPatient();

        $this->logOnDay($patient, today(), 'Rango normal');
        $this->logOnDay($patient, today()->subDay(), 'Rango normal');
        $this->logOnDay($patient, today()->subDays(2), 'Elevado - fuera de rango normal');
        $this->logOnDay($patient, today()->subDays(3), 'Rango normal');

        $streak = (new CalculatePatientStreak)->handle($patient->id);

        $this->assertSame(2, $streak);
    }

    public function test_streak_breaks_on_a_day_without_any_log(): void
    {
        $patient = $this->createPatient();

        $this->logOnDay($patient, today(), 'Rango normal');
        $this->logOnDay($patient, today()->subDay(), 'Rango normal');
        $this->logOnDay($patient, today()->subDays(3), 'Rango normal');

        $streak = (new CalculatePatientStreak)->handle($patient->id);

        $this->assertSame(2, $streak);
    }

    public function test_today_without_any_log_yet_does_not_break_the_streak(): void
    {
        $patient = $this->createPatient();

        $this->logOnDay($patient, today()->subDay(), 'Rango normal');
        $this->logOnDay($patient, today()->subDays(2), 'Rango normal');

        $streak = (new CalculatePatientStreak)->handle($patient->id);

        $this->assertSame(2, $streak);
    }

    public function test_today_with_an_out_of_range_reading_breaks_the_streak_immediately(): void
    {
        $patient = $this->createPatient();

        $this->logOnDay($patient, today(), 'Bajo - fuera de rango normal');
        $this->logOnDay($patient, today()->subDay(), 'Rango normal');

        $streak = (new CalculatePatientStreak)->handle($patient->id);

        $this->assertSame(0, $streak);
    }
}
