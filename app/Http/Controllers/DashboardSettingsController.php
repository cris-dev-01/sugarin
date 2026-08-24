<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Settings\GetDashboardSettingsSrv;
use App\Actions\Settings\UpdateDashboardSettingsSrv;
use App\DataTransferObjects\Settings\DashboardSettingsData;
use App\Http\Requests\Settings\UpdateDashboardSettingsRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class DashboardSettingsController extends Controller
{
    public function index(GetDashboardSettingsSrv $getSrv): Response
    {
        return Inertia::render('DashboardSettings/Index', [
            'settings' => $getSrv->handle(),
        ]);
    }

    public function update(
        UpdateDashboardSettingsRequest $request,
        UpdateDashboardSettingsSrv $updateSrv
    ): RedirectResponse {
        $settings = $updateSrv->handle(
            DashboardSettingsData::from($request->validated())
        );

        return redirect()->route('dashboard-settings.index')
            ->with('response', $settings);
    }
}
