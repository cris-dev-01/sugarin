<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\GlucoseRange;
use App\Models\User;
use App\Models\UserPatient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserPatient>
 */
class UserPatientFactory extends Factory
{
    protected $model = UserPatient::class;

    public function definition(): array
    {
        return [
            'document_type' => 'rut',
            'document' => (string) fake()->unique()->numberBetween(10_000_000, 25_000_000),
            'illness_found_at' => fake()->date(),
            'initial_max_glucose_value' => 140,
            'user_id' => User::factory(),
            'glucose_range_id' => GlucoseRange::factory(),
        ];
    }
}
