<?php

declare(strict_types=1);

namespace App\Actions\GlucoseLogs;

use App\DataTransferObjects\GlucoseLogs\ListGlucoseLogsDto;
use App\Models\UserGlucoseLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;
use Spatie\LaravelData\Optional;

class ListGlucoseLogsSrv
{
    use AsAction;

    public function handle(ListGlucoseLogsDto $dto): LengthAwarePaginator|Collection
    {
        $relations = array_merge(['userPatient', 'status'], $dto->relations);

        $query = UserGlucoseLog::query()
            ->when($dto->fields, fn (Builder $builder) => $builder->select($dto->fields))
            ->when(! $dto->filters instanceof Optional, fn (Builder $builder) => $builder->filter($dto->filters))
            ->with($relations)
            ->when($dto->includeTrashed, fn (Builder $builder) => $builder->withTrashed());

        return $dto->paginated ? $query->paginate(perPage: $dto->per_page, page: $dto->page) : $query->get();
    }
}
