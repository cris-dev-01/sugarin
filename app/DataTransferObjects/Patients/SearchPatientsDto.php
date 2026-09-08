<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Patients;

use Spatie\LaravelData\Data;

class SearchPatientsDto extends Data
{
    public function __construct(
        public string $query = '',
    ) {}
}
