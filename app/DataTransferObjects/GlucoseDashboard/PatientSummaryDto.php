<?php

declare(strict_types=1);

namespace App\DataTransferObjects\GlucoseDashboard;

use Spatie\LaravelData\Data;

class PatientSummaryDto extends Data
{
    public function __construct(
        public int $user_patient_id,
        public int $period = 30,
    ) {}
}
