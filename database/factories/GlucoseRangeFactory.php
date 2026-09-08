<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\GlucoseRange;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GlucoseRange>
 */
class GlucoseRangeFactory extends Factory
{
    protected $model = GlucoseRange::class;

    public function definition(): array
    {
        return [
            'alias' => fake()->unique()->words(2, true),
            'min_fasting_value' => 70,
            'max_fasting_value' => 100,
            'min_non_fasting_value' => 70,
            'max_non_fasting_value' => 140,
        ];
    }
}
