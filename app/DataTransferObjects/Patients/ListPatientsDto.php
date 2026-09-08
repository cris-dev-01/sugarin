<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Patients;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class ListPatientsDto extends Data
{
    public function __construct(
        public array|Optional $filters,
        public array $fields = ['*'],
        public array $relations = [],
        public bool $includeTrashed = false,
        public bool $paginated = true,
        public int $page = 1,
        public int $per_page = 30,
    ) {}
}
