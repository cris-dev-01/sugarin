<?php

declare(strict_types=1);

namespace App\Actions\Patients;

use App\DataTransferObjects\Patients\ListPatientsDto;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;
use Spatie\LaravelData\Optional;

class ListPatientsSrv
{
    use AsAction;

    public function handle(ListPatientsDto $dto): LengthAwarePaginator|Collection
    {
        $query = User::query()
            ->whereHas('roles', function ($query) {
                $query->where('name', 'Patient');
            })
            ->when($dto->fields, fn (Builder $builder) => $builder->select($dto->fields))
            ->when(! $dto->filters instanceof Optional, fn (Builder $builder) => $builder->filter($dto->filters))
            ->when($dto->relations, fn (Builder $builder) => $builder->with($dto->relations))
            ->when($dto->includeTrashed, fn (Builder $builder) => $builder->withTrashed());

        return $dto->paginated ? $query->paginate(perPage: $dto->per_page, page: $dto->page) : $query->get();
    }
}
