<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Enums\TimeBlock;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GlucoseLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'value' => $this->value,
            'time_block' => $this->time_block,
            'user_patient' => $this->whenLoaded('userPatient', fn () => [
                'id' => $this->userPatient->id,
                'name' => optional($this->userPatient->user)->name,
            ]),
            'status' => $this->whenLoaded('status', fn () => [
                'id' => $this->status->id,
                'name' => $this->status->name,
            ]),
            'is_abnormal' => $this->whenLoaded('status', fn () => $this->status->name !== 'Rango normal'),
            'range' => $this->when(
                $this->relationLoaded('userPatient') && $this->userPatient->relationLoaded('glucoseRange') && $this->userPatient->glucoseRange,
                fn () => $this->userPatient->glucoseRange->thresholdsFor(TimeBlock::from($this->time_block))
            ),
            'created_at' => $this->created_at?->format('d/m/Y H:i'),
        ];
    }
}
