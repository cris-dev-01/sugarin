<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Enums\TimeBlock;
use App\Models\GlucoseRange;
use Tests\TestCase;

class GlucoseRangeTest extends TestCase
{
    public function test_thresholds_for_fasting_uses_fasting_values(): void
    {
        $range = new GlucoseRange([
            'min_fasting_value' => 70,
            'max_fasting_value' => 100,
            'min_non_fasting_value' => 80,
            'max_non_fasting_value' => 140,
        ]);

        $this->assertSame(['min' => 70, 'max' => 100], $range->thresholdsFor(TimeBlock::FASTING));
    }

    public function test_thresholds_for_non_fasting_uses_non_fasting_values(): void
    {
        $range = new GlucoseRange([
            'min_fasting_value' => 70,
            'max_fasting_value' => 100,
            'min_non_fasting_value' => 80,
            'max_non_fasting_value' => 140,
        ]);

        $this->assertSame(['min' => 80, 'max' => 140], $range->thresholdsFor(TimeBlock::NON_FASTING));
    }
}
