<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GlucoseLogEntryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'value' => $this->value,
            'time_block' => $this->time_block,
            'status' => $this->whenLoaded('status', fn () => $this->status?->name),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
