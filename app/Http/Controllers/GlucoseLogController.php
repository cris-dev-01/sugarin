<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\GlucoseLogs\GetPatientGlucoseLogsSrv;
use App\Actions\GlucoseLogs\StoreGlucoseLogSrv;
use App\Actions\Patients\ListPatientsSrv;
use App\DataTransferObjects\GlucoseLogs\PatientLogsDto;
use App\DataTransferObjects\GlucoseLogs\StoreGlucoseLogDto;
use App\DataTransferObjects\Patients\ListPatientsDto;
use App\Http\Requests\GlucoseLogs\PatientLogsRequest;
use App\Http\Requests\GlucoseLogs\StoreGlucoseLogRequest;
use App\Http\Resources\GlucoseLogEntryResource;
use App\Http\Resources\GlucoseLogResource;
use App\Models\UserPatient;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class GlucoseLogController extends Controller
{
    public function index(ListPatientsSrv $listPatientsSrv): Response
    {
        return Inertia::render('GlucoseLogs/Index', [
            'patients' => $listPatientsSrv->handle(
                ListPatientsDto::from([
                    'fields' => ['*'],
                    'relations' => ['patient'],
                    'paginated' => false,
                ])
            ),
        ]);
    }

    public function store(
        StoreGlucoseLogRequest $request,
        StoreGlucoseLogSrv $storeSrv
    ): JsonResponse {
        $log = $storeSrv->handle(
            StoreGlucoseLogDto::from($request->validated())
        );

        $log->load(['userPatient.user', 'userPatient.glucoseRange', 'status']);

        return (new GlucoseLogResource($log))->response()->setStatusCode(201);
    }

    public function forPatient(
        UserPatient $patient,
        PatientLogsRequest $request,
        GetPatientGlucoseLogsSrv $logsSrv
    ): JsonResponse {
        abort_if(
            ! $request->user()->hasRole('Administrator') && $patient->id !== $request->user()->patient?->id,
            403
        );

        $logs = $logsSrv->handle(
            PatientLogsDto::from([
                ...$request->validated(),
                'user_patient_id' => $patient->id,
            ])
        );

        return GlucoseLogEntryResource::collection($logs)->response();
    }
}
