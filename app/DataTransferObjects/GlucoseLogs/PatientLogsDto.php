<?php

declare(strict_types=1);

namespace App\DataTransferObjects\GlucoseLogs;

use Spatie\LaravelData\Data;

class PatientLogsDto extends Data
{
    public function __construct(
        public int $user_patient_id,
        public int $page = 1,
        public ?string $date = null,
    ) {}
}
