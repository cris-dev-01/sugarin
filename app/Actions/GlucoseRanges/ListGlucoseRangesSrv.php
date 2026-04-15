<?php

declare(strict_types=1);

namespace App\Actions\GlucoseRanges;

use App\DataTransferObjects\GlucoseRanges\ListGlucoseRangesDto;
use App\Models\GlucoseRange;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;
use Spatie\LaravelData\Optional;

class ListGlucoseRangesSrv
{
    use AsAction;

    public function handle(ListGlucoseRangesDto $dto): LengthAwarePaginator|Collection
    {
        $query = GlucoseRange::query()
            ->when($dto->fields, fn (Builder $builder) => $builder->select($dto->fields))
            ->when(! $dto->filters instanceof Optional, fn (Builder $builder) => $builder->filter($dto->filters))
            ->when($dto->relations, fn (Builder $builder) => $builder->with($dto->relations))
            ->when($dto->includeTrashed, fn (Builder $builder) => $builder->withTrashed());

        return $dto->paginated ? $query->paginate(perPage: $dto->per_page, page: $dto->page) : $query->get();
    }
}
