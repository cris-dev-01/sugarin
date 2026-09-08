<?php

declare(strict_types=1);

namespace App\Actions\GlucoseDashboard;

use App\Actions\Settings\GetDashboardSettingsSrv;
use App\DataTransferObjects\GlucoseDashboard\TriagePatientsDto;
use App\Models\Status;
use App\Models\UserGlucoseLog;
use App\Models\UserPatient;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

class GetTriagePatientsByCriteriaSrv
{
    use AsAction;

    public function __construct(
        private readonly GetDashboardSettingsSrv $settingsSrv
    ) {}

    /**
     * @return array{criteria: string, patients: list<array<string, mixed>>}
     */
    public function handle(TriagePatientsDto $dto): array
    {
        $patients = match ($dto->criteria) {
            'low_recent' => $this->patientsWithLowRecentEvent(),
            'inactive' => $this->patientsWithoutRecentLog(),
            'good_control' => $this->patientsInGoodControl(),
        };

        return [
            'criteria' => $dto->criteria,
            'patients' => $patients,
        ];
    }

    /**
     * @return list<array{id: int, name: string, last_low_value: int, last_low_time_block: string, last_low_at: string}>
     */
    private function patientsWithLowRecentEvent(): array
    {
        $lowStatusId = Status::where('name', 'Bajo - fuera de rango normal')->value('id');
        $windowStart = now()->subHours($this->settingsSrv->handle()->recent_event_window_hours);

        return UserGlucoseLog::query()
            ->where('status_id', $lowStatusId)
            ->where('created_at', '>=', $windowStart)
            ->with('userPatient.user')
            ->orderByDesc('created_at')
            ->get()
            ->unique('user_patient_id')
            ->map(fn (UserGlucoseLog $log) => [
                'id' => $log->userPatient->id,
                'name' => $log->userPatient->user?->name ?? '',
                'last_low_value' => $log->value,
                'last_low_time_block' => $log->time_block,
                'last_low_at' => $log->created_at?->toIso8601String(),
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array{id: int, name: string, hours_since_last_log: float|null, last_log_at: string|null}>
     */
    private function patientsWithoutRecentLog(): array
    {
        $windowHours = $this->settingsSrv->handle()->recent_event_window_hours;
        $lastLogAt = $this->lastLogAtByPatient();

        return UserPatient::with('user')
            ->get()
            ->map(function (UserPatient $patient) use ($lastLogAt) {
                $lastCreatedAt = $lastLogAt->get($patient->id);

                return [
                    'id' => $patient->id,
                    'name' => $patient->user?->name ?? '',
                    'hours_since_last_log' => $lastCreatedAt ? now()->diffInHours($lastCreatedAt, true) : null,
                    'last_log_at' => $lastCreatedAt ? Carbon::parse($lastCreatedAt)->toIso8601String() : null,
                ];
            })
            ->filter(fn (array $patient) => $patient['hours_since_last_log'] === null || $patient['hours_since_last_log'] > $windowHours)
            ->sortByDesc(fn (array $patient) => $patient['hours_since_last_log'] ?? INF)
            ->values()
            ->all();
    }

    /**
     * @return list<array{id: int, name: string, in_range_percentage: float}>
     */
    private function patientsInGoodControl(): array
    {
        $goodControlThreshold = $this->settingsSrv->handle()->good_control_threshold;
        $periodStats = $this->periodStatsByPatient();

        return UserPatient::with('user')
            ->get()
            ->map(function (UserPatient $patient) use ($periodStats) {
                $periodStat = $periodStats->get($patient->id);

                return [
                    'id' => $patient->id,
                    'name' => $patient->user?->name ?? '',
                    'in_range_percentage' => $periodStat && (int) $periodStat->total > 0
                        ? round((int) $periodStat->normal_count / (int) $periodStat->total * 100, 1)
                        : null,
                ];
            })
            ->filter(fn (array $patient) => $patient['in_range_percentage'] !== null && $patient['in_range_percentage'] >= $goodControlThreshold * 100)
            ->sortByDesc('in_range_percentage')
            ->values()
            ->all();
    }

    private function lastLogAtByPatient(): Collection
    {
        return UserGlucoseLog::query()
            ->selectRaw('user_patient_id, MAX(created_at) as last_created_at')
            ->groupBy('user_patient_id')
            ->pluck('last_created_at', 'user_patient_id');
    }

    private function periodStatsByPatient(): Collection
    {
        $normalStatusId = Status::where('name', 'Rango normal')->value('id');
        $referenceDays = $this->settingsSrv->handle()->good_control_reference_period_days;

        return UserGlucoseLog::query()
            ->where('created_at', '>=', now()->subDays($referenceDays))
            ->selectRaw('user_patient_id, COUNT(*) as total, SUM(CASE WHEN status_id = ? THEN 1 ELSE 0 END) as normal_count', [$normalStatusId])
            ->groupBy('user_patient_id')
            ->get()
            ->keyBy('user_patient_id');
    }
}
