<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\GlucoseRanges\DeleteGlucoseRangeSrv;
use App\Actions\GlucoseRanges\ListGlucoseRangesSrv;
use App\Actions\GlucoseRanges\StoreGlucoseRangeSrv;
use App\Actions\GlucoseRanges\UpdateGlucoseRangeSrv;
use App\DataTransferObjects\GlucoseRanges\ListGlucoseRangesDto;
use App\DataTransferObjects\GlucoseRanges\StoreGlucoseRangeDto;
use App\DataTransferObjects\GlucoseRanges\UpdateGlucoseRangeDto;
use App\Http\Controllers\Controller;
use App\Http\Requests\GlucoseRanges\StoreGlucoseRangeRequest;
use App\Http\Requests\GlucoseRanges\UpdateGlucoseRangeRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class GlucoseRangeController extends Controller
{
    public function index(
        ListGlucoseRangesSrv $listGlucoseRangesSrv
    ): Response
    {
        return Inertia::render('GlucoseRanges/Index', [
            'glucoseRanges' => $listGlucoseRangesSrv->handle(
                ListGlucoseRangesDto::from([
                    'fields' => ['*'],
                    'relations' => [],
                    'paginated' => false,
                ])
            )
        ]);
    }

    public function store(
        StoreGlucoseRangeRequest $request,
        StoreGlucoseRangeSrv $storeSrv
    ): RedirectResponse
    {
        $glucoseRange = $storeSrv->handle(
            StoreGlucoseRangeDto::from(
                $request->validated()
            )
        );

        return redirect()->route('glucose-ranges.index')
            ->with('response', $glucoseRange);
    }

    public function update(
        int $id,
        UpdateGlucoseRangeRequest $request,
        UpdateGlucoseRangeSrv $updateSrv
    ): RedirectResponse
    {
        $glucoseRange = $updateSrv->handle(
            UpdateGlucoseRangeDto::from([
                ...$request->validated(),
                'id' => $id
            ])
        );

        return redirect()->route('glucose-ranges.index')
            ->with('response', $glucoseRange);
    }

    public function destroy(
        int $id,
        DeleteGlucoseRangeSrv $deleteSrv
    ): RedirectResponse
    {
        $glucoseRange = $deleteSrv->handle($id);

        return redirect()->route('glucose-ranges.index')
            ->with('response', $glucoseRange);
    }
}
