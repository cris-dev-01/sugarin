<?php

declare(strict_types=1);

namespace App\Actions\GlucoseDashboard;

use App\Actions\Settings\GetDashboardSettingsSrv;
use App\DataTransferObjects\GlucoseDashboard\PatientSummaryDto;
use App\Enums\TimeBlock;
use App\Models\UserGlucoseLog;
use App\Models\UserPatient;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

class GetPatientSummarySrv
{
    use AsAction;

    private const RECENT_LOGS_LIMIT = 20;

    public function __construct(
        private readonly CalculatePatientStreak $calculateStreak,
        private readonly GetDashboardSettingsSrv $settingsSrv
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function handle(PatientSummaryDto $dto): array
    {
        $patient = UserPatient::with('user')->findOrFail($dto->user_patient_id);

        $logs = $this->loadPeriodLogs($patient->id, $dto->period);
        $lastLog = $this->loadLastLog($patient->id);
        $recentLogs = $this->loadRecentLogs($patient->id);

        return [
            'patient' => $this->formatPatient($patient),
            'period_days' => $dto->period,
            'in_range_percentage' => $this->inRangePercentageByTimeBlock($logs),
            'events' => $this->eventsByTimeBlock($logs),
            'value_stats' => $this->valueStatsByTimeBlock($logs),
            'streak_days' => $this->calculateStreak->handle($patient->id),
            'adherence_percentage' => $this->adherencePercentage($dto->period, $logs->count()),
            'last_reading' => $this->formatLastReading($lastLog),
            'status_distribution' => $this->statusDistribution($logs),
            'recent_logs' => $this->formatLogEntries($recentLogs),
        ];
    }

    private function loadPeriodLogs(int $patientId, int $periodDays): Collection
    {
        return UserGlucoseLog::query()
            ->where('user_patient_id', $patientId)
            ->where('created_at', '>=', now()->subDays($periodDays))
            ->with('status')
            ->get();
    }

    private function loadLastLog(int $patientId): ?UserGlucoseLog
    {
        return UserGlucoseLog::query()
            ->where('user_patient_id', $patientId)
            ->with('status')
            ->latest()
            ->first();
    }

    private function loadRecentLogs(int $patientId): Collection
    {
        return UserGlucoseLog::query()
            ->where('user_patient_id', $patientId)
            ->with('status')
            ->latest()
            ->limit(self::RECENT_LOGS_LIMIT)
            ->get();
    }

    /**
     * @return array{id: int, name: string, email: string, formatted_document: string}
     */
    private function formatPatient(UserPatient $patient): array
    {
        return [
            'id' => $patient->id,
            'name' => $patient->user?->name ?? '',
            'email' => $patient->user?->email ?? '',
            'formatted_document' => $patient->formatted_document,
        ];
    }

    private function adherencePercentage(int $periodDays, int $loggedCount): float
    {
        $expectedLogs = $this->settingsSrv->handle()->expected_logs_per_day * $periodDays;

        return $expectedLogs > 0 ? round($loggedCount / $expectedLogs * 100, 1) : 0.0;
    }

    /**
     * @return array{value: int, time_block: string, status: string|null, created_at: string|null}|null
     */
    private function formatLastReading(?UserGlucoseLog $log): ?array
    {
        return $log ? $this->formatLogEntry($log) : null;
    }

    /**
     * @return list<array{id: int, value: int, time_block: string, status: string|null, created_at: string|null}>
     */
    private function formatLogEntries(Collection $logs): array
    {
        return $logs->map(fn (UserGlucoseLog $log) => $this->formatLogEntry($log))->values()->all();
    }

    /**
     * @return array{id: int, value: int, time_block: string, status: string|null, created_at: string|null}
     */
    private function formatLogEntry(UserGlucoseLog $log): array
    {
        return [
            'id' => $log->id,
            'value' => $log->value,
            'time_block' => $log->time_block,
            'status' => $log->status?->name,
            'created_at' => $log->created_at?->toIso8601String(),
        ];
    }

    /**
     * @return array<string, float|null>
     */
    private function inRangePercentageByTimeBlock(Collection $logs): array
    {
        $result = ['combined' => $this->normalPercentage($logs)];

        foreach (TimeBlock::cases() as $timeBlock) {
            $result[$timeBlock->value] = $this->normalPercentage(
                $logs->where('time_block', $timeBlock->value)
            );
        }

        return $result;
    }

    private function normalPercentage(Collection $logs): ?float
    {
        if ($logs->isEmpty()) {
            return null;
        }

        $normalCount = $logs->filter(fn (UserGlucoseLog $log) => $log->status?->name === 'Rango normal')->count();

        return round($normalCount / $logs->count() * 100, 1);
    }

    /**
     * @return array{bajo: array<string, int>, elevado: array<string, int>}
     */
    private function eventsByTimeBlock(Collection $logs): array
    {
        return [
            'bajo' => $this->countByStatusAndTimeBlock($logs, 'Bajo - fuera de rango normal'),
            'elevado' => $this->countByStatusAndTimeBlock($logs, 'Elevado - fuera de rango normal'),
        ];
    }

    /**
     * @return array<string, int>
     */
    private function countByStatusAndTimeBlock(Collection $logs, string $statusName): array
    {
        $filtered = $logs->filter(fn (UserGlucoseLog $log) => $log->status?->name === $statusName);

        $result = ['total' => $filtered->count()];

        foreach (TimeBlock::cases() as $timeBlock) {
            $result[$timeBlock->value] = $filtered->where('time_block', $timeBlock->value)->count();
        }

        return $result;
    }

    /**
     * @return array<string, array{avg: float|null, stddev: float|null}>
     */
    private function valueStatsByTimeBlock(Collection $logs): array
    {
        $result = [];

        foreach (TimeBlock::cases() as $timeBlock) {
            $values = $logs->where('time_block', $timeBlock->value)->pluck('value')->all();
            $result[$timeBlock->value] = $this->stats($values);
        }

        return $result;
    }

    /**
     * @param  list<int>  $values
     * @return array{avg: float|null, stddev: float|null}
     */
    private function stats(array $values): array
    {
        $count = count($values);

        if ($count === 0) {
            return ['avg' => null, 'stddev' => null];
        }

        $avg = array_sum($values) / $count;
        $variance = array_sum(array_map(fn ($value) => ($value - $avg) ** 2, $values)) / $count;

        return [
            'avg' => round($avg, 1),
            'stddev' => round(sqrt($variance), 1),
        ];
    }

    /**
     * @return list<array{status: string, count: int, percentage: float}>
     */
    private function statusDistribution(Collection $logs): array
    {
        $total = $logs->count();

        return $logs
            ->groupBy(fn (UserGlucoseLog $log) => $log->status?->name ?? 'Sin estado')
            ->map(fn (Collection $group, string $statusName) => [
                'status' => $statusName,
                'count' => $group->count(),
                'percentage' => $total > 0 ? round($group->count() / $total * 100, 1) : 0.0,
            ])
            ->values()
            ->all();
    }
}
