<?php

declare(strict_types=1);

namespace App\DataTransferObjects\GlucoseDashboard;

use Spatie\LaravelData\Data;

class TriagePatientsDto extends Data
{
    public function __construct(
        public string $criteria,
    ) {}
}
