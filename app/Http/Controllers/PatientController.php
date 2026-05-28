<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\GlucoseRanges\ListGlucoseRangesSrv;
use App\Actions\Patients\CheckPatientSrv;
use App\Actions\Patients\ListPatientsSrv;
use App\Actions\Patients\StorePatientSrv;
use App\DataTransferObjects\GlucoseRanges\ListGlucoseRangesDto;
use App\DataTransferObjects\Patients\CheckPatientDto;
use App\DataTransferObjects\Patients\ListPatientsDto;
use App\DataTransferObjects\Patients\StorePatientDto;
use App\Http\Controllers\Controller;
use App\Http\Requests\Patients\StorePatientRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class PatientController extends Controller
{
    public function index(
        ListGlucoseRangesSrv $listGlucoseRangesSrv,
        ListPatientsSrv $listPatientsSrv
    ): Response
    {
        return Inertia::render('Patients/Index', [
            'patients' => $listPatientsSrv->handle(
                ListPatientsDto::from([
                    'fields' => ['*'],
                    'relations' => ['patient'],
                    'paginated' => false,
                ])
            ),
            'glucoseRanges' => $listGlucoseRangesSrv->handle(
                ListGlucoseRangesDto::from([
                    'fields' => ['*'],
                    'relations' => [],
                    'paginated' => false,
                ])
            )
        ]);
    }

    public function checkPatient(
        string $document,
        CheckPatientSrv $checkSrv
    ): JsonResponse
    {
        return response()
            ->json([
                'response' => $checkSrv->handle(
                    CheckPatientDto::from([
                        'document' => $document
                    ])
                )
            ]);
    }

    public function store(
        StorePatientRequest $request,
        StorePatientSrv $storeSrv
    ): RedirectResponse
    {
        $patient = $storeSrv->handle(
            StorePatientDto::from(
                $request->validated()
            )
        );

        return redirect()->route('patients.index')
            ->with('response', $patient);
    }
}
