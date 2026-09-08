<?php

declare(strict_types=1);

namespace App\Actions\Settings;

use App\DataTransferObjects\Settings\DashboardSettingsData;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Lorisleiva\Actions\Concerns\AsAction;

class GetDashboardSettingsSrv
{
    use AsAction;

    public const CACHE_KEY = 'settings.dashboard';

    private const KEYS = [
        'recent_event_window_hours',
        'good_control_threshold',
        'good_control_reference_period_days',
        'expected_logs_per_day',
    ];

    public function handle(): DashboardSettingsData
    {
        return Cache::rememberForever(self::CACHE_KEY, fn () => $this->load());
    }

    private function load(): DashboardSettingsData
    {
        $settings = Setting::query()
            ->whereIn('key', self::KEYS)
            ->get()
            ->keyBy('key');

        return DashboardSettingsData::from(
            collect(self::KEYS)
                ->mapWithKeys(fn (string $key) => [$key => $settings->get($key)?->casted_value])
                ->all()
        );
    }
}
