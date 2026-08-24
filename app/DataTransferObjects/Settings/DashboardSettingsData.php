<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Settings;

use Spatie\LaravelData\Data;

class DashboardSettingsData extends Data
{
    public function __construct(
        public int $recent_event_window_hours,
        public float $good_control_threshold,
        public int $good_control_reference_period_days,
        public int $expected_logs_per_day,
    ) {}
}
