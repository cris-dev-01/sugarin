<?php

declare(strict_types=1);

namespace App\Actions\GlucoseLogs;

use App\DataTransferObjects\GlucoseLogs\PatientLogsDto;
use App\Models\UserGlucoseLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Lorisleiva\Actions\Concerns\AsAction;

class GetPatientGlucoseLogsSrv
{
    use AsAction;

    private const PER_PAGE = 15;

    public function handle(PatientLogsDto $dto): LengthAwarePaginator
    {
        return UserGlucoseLog::query()
            ->where('user_patient_id', $dto->user_patient_id)
            ->when($dto->date, fn (Builder $query) => $query->whereDate('created_at', $dto->date))
            ->with('status')
            ->latest()
            ->paginate(perPage: self::PER_PAGE, page: $dto->page);
    }
}
