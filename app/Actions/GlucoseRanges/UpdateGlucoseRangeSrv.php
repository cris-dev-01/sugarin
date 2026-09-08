<?php

declare(strict_types=1);

namespace App\Actions\GlucoseRanges;

use App\DataTransferObjects\GlucoseRanges\UpdateGlucoseRangeDto;
use App\Models\GlucoseRange;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;
use Throwable;

class UpdateGlucoseRangeSrv
{
    use AsAction;

    private GlucoseRange $glucoseRange;

    /**
     * @throws Throwable
     */
    public function handle(UpdateGlucoseRangeDto $dto): GlucoseRange|Model
    {
        DB::transaction(function () use ($dto) {
            $this->getRange($dto->id)
                ->update($dto);
        });

        return $this->glucoseRange;
    }

    private function getRange(int $id): self
    {
        $this->glucoseRange = GlucoseRange::findOrFail($id);

        return $this;
    }

    private function update(UpdateGlucoseRangeDto $dto): void
    {
        $this->glucoseRange->update(
            $dto->toArray()
        );
    }
}
