<?php

declare(strict_types=1);

namespace App\Actions\GlucoseRanges;

use App\Models\GlucoseRange;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;
use Throwable;

class DeleteGlucoseRangeSrv
{
    use AsAction;

    private GlucoseRange $glucoseRange;

    /**
     * @throws Throwable
     */
    public function handle(int $id): GlucoseRange|Model
    {
        DB::transaction(function () use ($id) {
            $this->getRange($id)
                ->delete();
        });

        return $this->glucoseRange;
    }

    private function getRange(int $id): self
    {
        $this->glucoseRange = GlucoseRange::findOrFail($id);

        return $this;
    }

    private function delete(): void
    {
        $this->glucoseRange->delete();
    }
}
