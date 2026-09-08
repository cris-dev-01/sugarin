<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Profile;

use Spatie\LaravelData\Data;

class UpdatePasswordDto extends Data
{
    public function __construct(
        public int $id,
        public string $current_password,
        public string $password,
    ) {}
}
