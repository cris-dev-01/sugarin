<?php

declare(strict_types=1);

namespace App\Actions\GlucoseDashboard;

use App\Models\Status;
use App\Models\UserGlucoseLog;
use App\Models\UserPatient;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

class GetTriageOverviewSrv
{
    use AsAction;

    /**
     * @return array{
     *     low_recent_count: int,
     *     inactive_count: int,
     *     good_control_percentage: float,
     *     patients: list<array{
     *         id: int,
     *         name: string,
     *         has_low_recent: bool,
     *         hours_since_last_log: float|null,
     *         in_range_percentage: float|null,
     *     }>,
     * }
     */
    public function handle(): array
    {
        $windowHours = (int) config('glucose_dashboard.recent_event_window_hours');
        $goodControlThreshold = (float) config('glucose_dashboard.good_control_threshold');

        $patients = $this->buildRiskProfiles($windowHours);

        return [
            'low_recent_count' => $patients->where('has_low_recent', true)->count(),
            'inactive_count' => $patients->where('is_inactive', true)->count(),
            'good_control_percentage' => $this->goodControlPercentage($patients, $goodControlThreshold),
            'patients' => $this->formatPatients($patients),
        ];
    }

    private function buildRiskProfiles(int $windowHours): Collection
    {
        $lastLogAt = $this->lastLogAtByPatient();
        $lowRecentPatientIds = $this->lowRecentPatientIds($windowHours);
        $periodStats = $this->periodStatsByPatient();

        return UserPatient::with('user')
            ->get()
            ->map(fn (UserPatient $patient) => $this->buildRiskProfile(
                $patient,
                $lastLogAt,
                $lowRecentPatientIds,
                $periodStats,
                $windowHours
            ))
            ->sort($this->riskComparator())
            ->values();
    }

    /**
     * @return array{
     *     id: int,
     *     name: string,
     *     has_low_recent: bool,
     *     hours_since_last_log: float|null,
     *     in_range_percentage: float|null,
     *     is_inactive: bool,
     * }
     */
    private function buildRiskProfile(
        UserPatient $patient,
        Collection $lastLogAt,
        Collection $lowRecentPatientIds,
        Collection $periodStats,
        int $windowHours
    ): array {
        $lastCreatedAt = $lastLogAt->get($patient->id);
        $hoursSinceLastLog = $lastCreatedAt ? now()->diffInHours($lastCreatedAt, true) : null;

        return [
            'id' => $patient->id,
            'name' => $patient->user?->name ?? '',
            'has_low_recent' => $lowRecentPatientIds->has($patient->id),
            'hours_since_last_log' => $hoursSinceLastLog,
            'in_range_percentage' => $this->periodInRangePercentage($periodStats->get($patient->id)),
            'is_inactive' => $hoursSinceLastLog === null || $hoursSinceLastLog > $windowHours,
        ];
    }

    private function periodInRangePercentage(?object $periodStat): ?float
    {
        if (! $periodStat || (int) $periodStat->total === 0) {
            return null;
        }

        return round((int) $periodStat->normal_count / (int) $periodStat->total * 100, 1);
    }

    private function riskComparator(): callable
    {
        return function (array $a, array $b) {
            if ($a['has_low_recent'] !== $b['has_low_recent']) {
                return $a['has_low_recent'] ? -1 : 1;
            }

            $hoursA = $a['hours_since_last_log'] ?? INF;
            $hoursB = $b['hours_since_last_log'] ?? INF;

            if ($hoursA !== $hoursB) {
                return $hoursA > $hoursB ? -1 : 1;
            }

            return ($a['in_range_percentage'] ?? 0) <=> ($b['in_range_percentage'] ?? 0);
        };
    }

    private function goodControlPercentage(Collection $patients, float $threshold): float
    {
        $candidates = $patients->filter(fn (array $patient) => $patient['in_range_percentage'] !== null);

        if ($candidates->isEmpty()) {
            return 0.0;
        }

        $goodCount = $candidates->filter(
            fn (array $patient) => $patient['in_range_percentage'] >= $threshold * 100
        )->count();

        return round($goodCount / $candidates->count() * 100, 1);
    }

    /**
     * @return list<array{id: int, name: string, has_low_recent: bool, hours_since_last_log: float|null, in_range_percentage: float|null}>
     */
    private function formatPatients(Collection $patients): array
    {
        return $patients->map(fn (array $patient) => [
            'id' => $patient['id'],
            'name' => $patient['name'],
            'has_low_recent' => $patient['has_low_recent'],
            'hours_since_last_log' => $patient['hours_since_last_log'],
            'in_range_percentage' => $patient['in_range_percentage'],
        ])->all();
    }

    private function lastLogAtByPatient(): Collection
    {
        return UserGlucoseLog::query()
            ->selectRaw('user_patient_id, MAX(created_at) as last_created_at')
            ->groupBy('user_patient_id')
            ->pluck('last_created_at', 'user_patient_id');
    }

    private function lowRecentPatientIds(int $windowHours): Collection
    {
        $lowStatusId = Status::where('name', 'Bajo - fuera de rango normal')->value('id');

        return UserGlucoseLog::query()
            ->where('status_id', $lowStatusId)
            ->where('created_at', '>=', now()->subHours($windowHours))
            ->distinct()
            ->pluck('user_patient_id')
            ->flip();
    }

    private function periodStatsByPatient(): Collection
    {
        $normalStatusId = Status::where('name', 'Rango normal')->value('id');

        return UserGlucoseLog::query()
            ->where('created_at', '>=', now()->subDays((int) config('glucose_dashboard.good_control_reference_period_days')))
            ->selectRaw('user_patient_id, COUNT(*) as total, SUM(CASE WHEN status_id = ? THEN 1 ELSE 0 END) as normal_count', [$normalStatusId])
            ->groupBy('user_patient_id')
            ->get()
            ->keyBy('user_patient_id');
    }
}
