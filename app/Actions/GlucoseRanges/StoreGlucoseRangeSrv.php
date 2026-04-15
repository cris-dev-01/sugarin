<?php

declare(strict_types=1);

namespace App\Actions\GlucoseRanges;

use App\DataTransferObjects\GlucoseRanges\StoreGlucoseRangeDto;
use App\Models\GlucoseRange;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;
use Throwable;

class StoreGlucoseRangeSrv
{
    use AsAction;

    private GlucoseRange $glucoseRange;

    /**
     * @throws Throwable
     */
    public function handle(StoreGlucoseRangeDto $dto): GlucoseRange|Model
    {
        DB::transaction(function () use ($dto) {
            $this->store($dto);
        });

        return $this->glucoseRange;
    }

    private function store(StoreGlucoseRangeDto $dto): void
    {
        $this->glucoseRange = GlucoseRange::create([
            'min_fasting_value' => $dto->min_fasting_value,
            'max_fasting_value' => $dto->max_fasting_value,
            'min_non_fasting_value' => $dto->min_non_fasting_value,
            'max_non_fasting_value' => $dto->max_non_fasting_value,
        ]);
    }
}
