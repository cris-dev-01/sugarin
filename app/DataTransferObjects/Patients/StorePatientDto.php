<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Patients;

use Spatie\LaravelData\Data;

class StorePatientDto extends Data
{
    public function __construct(
        public int $glucose_range_id,
        public string $name,
        public string $email,
        public string $document,
        public string $illness_found_at,
        public int $initial_max_glucose_value
    ) {}
}
