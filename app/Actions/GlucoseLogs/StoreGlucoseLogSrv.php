<?php

declare(strict_types=1);

namespace App\Actions\GlucoseLogs;

use App\DataTransferObjects\GlucoseLogs\StoreGlucoseLogDto;
use App\Enums\TimeBlock;
use App\Models\Status;
use App\Models\UserGlucoseLog;
use App\Models\UserPatient;
use App\Notifications\GlucoseLogRegisteredNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;
use Throwable;

class StoreGlucoseLogSrv
{
    use AsAction;

    private UserPatient $patient;

    private TimeBlock $timeBlock;

    private Status $status;

    /**
     * @throws Throwable
     */
    public function handle(StoreGlucoseLogDto $dto): UserGlucoseLog
    {
        $this->getPatient($dto->user_patient_id)
            ->getTimeBlock();

        if (is_null($this->patient->glucoseRange)) {
            throw ValidationException::withMessages([
                'user_patient_id' => 'El paciente no tiene un rango de glucosa configurado.',
            ]);
        }

        $this->getStatus($dto->value);

        $log = DB::transaction(function () use ($dto) {
            return UserGlucoseLog::create([
                'value' => $dto->value,
                'time_block' => $this->timeBlock->value,
                'user_patient_id' => $dto->user_patient_id,
                'status_id' => $this->status->id,
            ]);
        });

        $this->notifyPatient($log);

        return $log;
    }

    private function getPatient(int $id): self
    {
        $this->patient = UserPatient::with(['glucoseRange', 'user'])->findOrFail($id);

        return $this;
    }

    private function notifyPatient(UserGlucoseLog $log): void
    {
        $log->setRelation('status', $this->status);
        $this->patient->user->notify(new GlucoseLogRegisteredNotification($log));
    }

    private function getTimeBlock(): self
    {
        $this->timeBlock = TimeBlock::fromHour(now()->hour);

        return $this;
    }

    private function getStatus(int $value): void
    {
        $thresholds = $this->patient->glucoseRange->thresholdsFor($this->timeBlock);

        $statusName = match (true) {
            $value < $thresholds['min'] => 'Bajo - fuera de rango normal',
            $value > $thresholds['max'] => 'Elevado - fuera de rango normal',
            default => 'Rango normal',
        };

        $this->status = Status::where('name', $statusName)->firstOrFail();
    }
}
