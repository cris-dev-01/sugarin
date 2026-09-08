<?php

declare(strict_types=1);

namespace App\DataTransferObjects\GlucoseRanges;

use Spatie\LaravelData\Data;

class StoreGlucoseRangeDto extends Data
{
    public function __construct(
        public string $alias,
        public int $min_fasting_value,
        public int $max_fasting_value,
        public int $min_non_fasting_value,
        public int $max_non_fasting_value
    ) {}
}
