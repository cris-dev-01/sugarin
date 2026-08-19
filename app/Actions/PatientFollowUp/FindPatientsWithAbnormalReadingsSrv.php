<?php

declare(strict_types=1);

namespace App\Actions\PatientFollowUp;

use App\Models\UserGlucoseLog;
use App\Models\UserPatient;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

class FindPatientsWithAbnormalReadingsSrv
{
    use AsAction;

    private const ABNORMAL_STATUSES = [
        'Elevado - fuera de rango normal',
        'Bajo - fuera de rango normal',
    ];

    /**
     * @return Collection<int, array{patient: UserPatient, abnormal_status_names: list<string>}>
     */
    public function handle(int $windowHours): Collection
    {
        $statusNamesByPatient = $this->abnormalStatusNamesByPatient($windowHours);

        return UserPatient::with('user')
            ->whereIn('id', $statusNamesByPatient->keys())
            ->get()
            ->map(fn (UserPatient $patient) => [
                'patient' => $patient,
                'abnormal_status_names' => $statusNamesByPatient->get($patient->id),
            ])
            ->values();
    }

    /**
     * @return Collection<int, list<string>>
     */
    private function abnormalStatusNamesByPatient(int $windowHours): Collection
    {
        return UserGlucoseLog::query()
            ->join('statuses', 'statuses.id', '=', 'user_glucose_logs.status_id')
            ->whereIn('statuses.name', self::ABNORMAL_STATUSES)
            ->where('user_glucose_logs.created_at', '>=', now()->subHours($windowHours))
            ->select('user_glucose_logs.user_patient_id', 'statuses.name')
            ->distinct()
            ->get()
            ->groupBy('user_patient_id')
            ->map(fn (Collection $rows) => $rows->pluck('name')->values()->all());
    }
}
