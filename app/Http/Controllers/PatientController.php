<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\GlucoseRanges\ListGlucoseRangesSrv;
use App\Actions\Patients\CheckPatientSrv;
use App\Actions\Patients\DeletePatientSrv;
use App\Actions\Patients\ListPatientsSrv;
use App\Actions\Patients\SearchPatientsSrv;
use App\Actions\Patients\StorePatientSrv;
use App\Actions\Patients\UpdatePatientSrv;
use App\DataTransferObjects\GlucoseRanges\ListGlucoseRangesDto;
use App\DataTransferObjects\Patients\CheckPatientDto;
use App\DataTransferObjects\Patients\ListPatientsDto;
use App\DataTransferObjects\Patients\SearchPatientsDto;
use App\DataTransferObjects\Patients\StorePatientDto;
use App\DataTransferObjects\Patients\UpdatePatientDto;
use App\Http\Requests\Patients\SearchPatientsRequest;
use App\Http\Requests\Patients\StorePatientRequest;
use App\Http\Requests\Patients\UpdatePatientRequest;
use App\Http\Resources\PatientSearchResultResource;
use App\Models\UserPatient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class PatientController extends Controller
{
    public function index(
        ListGlucoseRangesSrv $listGlucoseRangesSrv,
        ListPatientsSrv $listPatientsSrv
    ): Response {
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
            ),
        ]);
    }

    public function search(
        SearchPatientsRequest $request,
        SearchPatientsSrv $searchSrv
    ): JsonResponse {
        return (new PatientSearchResultResource(
            $searchSrv->handle(
                SearchPatientsDto::from([
                    'query' => $request->validated('q', ''),
                ])
            )
        ))->response();
    }

    public function checkPatient(
        string $document,
        CheckPatientSrv $checkSrv
    ): JsonResponse {
        return response()
            ->json([
                'response' => $checkSrv->handle(
                    CheckPatientDto::from([
                        'document' => $document,
                        'exclude' => request()->query('exclude'),
                    ])
                ),
            ]);
    }

    public function store(
        StorePatientRequest $request,
        StorePatientSrv $storeSrv
    ): RedirectResponse {
        $patient = $storeSrv->handle(
            StorePatientDto::from(
                $request->validated()
            )
        );

        return redirect()->route('patients.index')
            ->with('response', $patient);
    }

    public function update(
        int $id,
        UpdatePatientRequest $request,
        UpdatePatientSrv $updateSrv
    ): RedirectResponse {
        $patient = $updateSrv->handle(
            UpdatePatientDto::from([
                ...$request->validated(),
                'id' => $id,
            ])
        );

        return redirect()->route('patients.index')
            ->with('response', $patient);
    }

    public function destroy(
        UserPatient $patient,
        DeletePatientSrv $deleteSrv
    ): RedirectResponse {
        $deleted = $deleteSrv->handle($patient);

        return redirect()->route('patients.index')
            ->with('response', $deleted);
    }
}
