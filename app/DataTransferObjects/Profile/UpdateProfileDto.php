<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Profile;

use Spatie\LaravelData\Data;

class UpdateProfileDto extends Data
{
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
    ) {}
}
