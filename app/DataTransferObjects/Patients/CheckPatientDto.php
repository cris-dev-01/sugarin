<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Patients;

use Spatie\LaravelData\Data;

class CheckPatientDto extends Data
{
    public function __construct(
        public string $document,
    ) {}

    public static function rules(): array
    {
        return [
            'document' => ['string', 'regex:/^\d{1,2}\.\d{3}\.\d{3}-[\dKk]$/'],
        ];
    }
}
