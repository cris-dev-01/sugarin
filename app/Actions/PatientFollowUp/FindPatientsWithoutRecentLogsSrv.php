<?php

declare(strict_types=1);

namespace App\Actions\PatientFollowUp;

use App\Models\UserGlucoseLog;
use App\Models\UserPatient;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

class FindPatientsWithoutRecentLogsSrv
{
    use AsAction;

    /**
     * @return Collection<int, array{patient: UserPatient, days_since_last_log: int|null}>
     */
    public function handle(int $days): Collection
    {
        $threshold = now()->subDays($days);
        $lastLogAt = $this->lastLogAtByPatient();

        return UserPatient::with('user')
            ->get()
            ->map(fn (UserPatient $patient) => [
                'patient' => $patient,
                'last_log_at' => $lastLogAt->get($patient->id),
            ])
            ->filter(fn (array $entry) => is_null($entry['last_log_at']) || Carbon::parse($entry['last_log_at'])->lt($threshold))
            ->map(fn (array $entry) => [
                'patient' => $entry['patient'],
                'days_since_last_log' => $entry['last_log_at']
                    ? (int) Carbon::parse($entry['last_log_at'])->diffInDays(now())
                    : null,
            ])
            ->values();
    }

    private function lastLogAtByPatient(): Collection
    {
        return UserGlucoseLog::query()
            ->selectRaw('user_patient_id, MAX(created_at) as last_created_at')
            ->groupBy('user_patient_id')
            ->pluck('last_created_at', 'user_patient_id');
    }
}
