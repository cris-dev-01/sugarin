<?php

declare(strict_types=1);

namespace App\Actions\GlucoseDashboard;

use App\Models\Status;
use App\Models\UserGlucoseLog;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

class CalculatePatientStreak
{
    use AsAction;

    /**
     * Racha de días consecutivos, hasta hoy, en que todas las lecturas del paciente
     * fueron "Rango normal". Un día sin ninguna lectura rompe la racha, salvo que sea
     * el día de hoy y aún no tenga lecturas (no se penaliza un día en curso todavía sin registrar).
     */
    public function handle(int $userPatientId): int
    {
        $dailyStats = $this->dailyStats($userPatientId);
        $day = $this->startingDay($dailyStats);

        return $this->countConsecutiveNormalDays($dailyStats, $day);
    }

    private function dailyStats(int $userPatientId): Collection
    {
        $normalStatusId = Status::where('name', 'Rango normal')->value('id');

        return UserGlucoseLog::query()
            ->where('user_patient_id', $userPatientId)
            ->where('created_at', '>=', now()->subYear())
            ->selectRaw('DATE(created_at) as log_date, COUNT(*) as total, SUM(CASE WHEN status_id = ? THEN 1 ELSE 0 END) as normal_count', [$normalStatusId])
            ->groupBy('log_date')
            ->get()
            ->keyBy(fn ($row) => (string) $row->log_date);
    }

    private function startingDay(Collection $dailyStats): Carbon
    {
        $today = today();

        return $dailyStats->has($today->toDateString()) ? $today : $today->subDay();
    }

    private function countConsecutiveNormalDays(Collection $dailyStats, Carbon $day): int
    {
        $streak = 0;

        while (true) {
            $stat = $dailyStats->get($day->toDateString());

            if (! $stat || (int) $stat->normal_count !== (int) $stat->total) {
                break;
            }

            $streak++;
            $day = $day->subDay();
        }

        return $streak;
    }
}
