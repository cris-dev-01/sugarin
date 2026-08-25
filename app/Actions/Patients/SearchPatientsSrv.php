<?php

declare(strict_types=1);

namespace App\Actions\Patients;

use App\DataTransferObjects\Patients\SearchPatientsDto;
use App\Models\UserPatient;
use Illuminate\Database\Eloquent\Builder;
use Lorisleiva\Actions\Concerns\AsAction;

class SearchPatientsSrv
{
    use AsAction;

    private const MIN_QUERY_LENGTH = 2;

    private const RESULTS_LIMIT = 8;

    /**
     * @return list<array{id: int, name: string, formatted_document: string}>
     */
    public function handle(SearchPatientsDto $dto): array
    {
        $query = trim($dto->query);

        if (mb_strlen($query) < self::MIN_QUERY_LENGTH) {
            return [];
        }

        $digitsOnly = preg_replace('/[.\-\s]/', '', $query);

        $patients = UserPatient::query()
            ->with('user')
            ->when(
                ctype_digit($digitsOnly),
                fn (Builder $builder) => $builder->where('document', 'like', "%{$digitsOnly}%"),
                fn (Builder $builder) => $builder->whereHas('user', fn (Builder $userQuery) => $userQuery->where('name', 'like', "%{$query}%"))
            )
            ->get()
            ->sortBy(fn (UserPatient $patient) => $patient->user?->name)
            ->take(self::RESULTS_LIMIT);

        return $patients->map(fn (UserPatient $patient) => [
            'id' => $patient->id,
            'name' => $patient->user?->name ?? '',
            'formatted_document' => $patient->formatted_document,
        ])->values()->all();
    }
}
