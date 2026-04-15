<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\GlucoseRanges\ListGlucoseRangesSrv;
use App\Actions\GlucoseRanges\StoreGlucoseRangeSrv;
use App\DataTransferObjects\GlucoseRanges\ListGlucoseRangesDto;
use App\DataTransferObjects\GlucoseRanges\StoreGlucoseRangeDto;
use App\Http\Controllers\Controller;
use App\Http\Requests\GlucoseRanges\StoreGlucoseRangeRequest;
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
}
