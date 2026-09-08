<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            ['key' => 'recent_event_window_hours', 'value' => '48', 'type' => 'integer'],
            ['key' => 'good_control_threshold', 'value' => '0.8', 'type' => 'float'],
            ['key' => 'good_control_reference_period_days', 'value' => '30', 'type' => 'integer'],
            ['key' => 'expected_logs_per_day', 'value' => '2', 'type' => 'integer'],
        ];

        foreach ($settings as $setting) {
            Setting::query()->updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
