<?php

declare(strict_types=1);

namespace App\Actions\Settings;

use App\DataTransferObjects\Settings\DashboardSettingsData;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateDashboardSettingsSrv
{
    use AsAction;

    public function handle(DashboardSettingsData $dto): DashboardSettingsData
    {
        DB::transaction(function () use ($dto) {
            $this->upsert('recent_event_window_hours', (string) $dto->recent_event_window_hours, 'integer');
            $this->upsert('good_control_threshold', (string) $dto->good_control_threshold, 'float');
            $this->upsert('good_control_reference_period_days', (string) $dto->good_control_reference_period_days, 'integer');
            $this->upsert('expected_logs_per_day', (string) $dto->expected_logs_per_day, 'integer');
        });

        Cache::forget(GetDashboardSettingsSrv::CACHE_KEY);

        return $dto;
    }

    private function upsert(string $key, string $value, string $type): void
    {
        Setting::query()->updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'type' => $type]
        );
    }
}
