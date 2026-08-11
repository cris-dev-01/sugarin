<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\TimeBlock;
use App\Models\Status;
use App\Models\UserGlucoseLog;
use App\Models\UserPatient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserGlucoseLog>
 */
class UserGlucoseLogFactory extends Factory
{
    protected $model = UserGlucoseLog::class;

    public function definition(): array
    {
        return [
            'value' => fake()->numberBetween(70, 100),
            'time_block' => TimeBlock::FASTING->value,
            'user_patient_id' => UserPatient::factory(),
            'status_id' => Status::factory(),
        ];
    }
}
