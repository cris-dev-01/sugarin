<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\GlucoseDashboard\GetPatientSummarySrv;
use App\Actions\GlucoseDashboard\GetTriageOverviewSrv;
use App\Actions\GlucoseDashboard\GetTriagePatientsByCriteriaSrv;
use App\DataTransferObjects\GlucoseDashboard\PatientSummaryDto;
use App\DataTransferObjects\GlucoseDashboard\TriagePatientsDto;
use App\Http\Requests\GlucoseDashboard\PatientSummaryRequest;
use App\Http\Requests\GlucoseDashboard\TriagePatientsRequest;
use App\Http\Resources\PatientSummaryResource;
use App\Http\Resources\TriagePatientsResource;
use App\Models\UserPatient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request, GetTriageOverviewSrv $triageSrv, GetPatientSummarySrv $summarySrv): Response
    {
        $triage = $triageSrv->handle();

        $visiblePatientIds = collect($triage['patients'])->pluck('id');
        $requestedPatientId = $request->integer('patient') ?: null;

        $selectedPatientId = $requestedPatientId && $visiblePatientIds->contains($requestedPatientId)
            ? $requestedPatientId
            : $visiblePatientIds->first();

        $summary = $selectedPatientId
            ? $summarySrv->handle(PatientSummaryDto::from(['user_patient_id' => $selectedPatientId]))
            : null;

        return Inertia::render('Dashboard/Index', [
            'triage' => $triage,
            'summary' => $summary,
        ]);
    }

    public function patientSummary(
        UserPatient $patient,
        PatientSummaryRequest $request,
        GetPatientSummarySrv $summarySrv
    ): JsonResponse {
        $summary = $summarySrv->handle(
            PatientSummaryDto::from([
                ...$request->validated(),
                'user_patient_id' => $patient->id,
            ])
        );

        return (new PatientSummaryResource($summary))->response();
    }

    public function triagePatients(
        TriagePatientsRequest $request,
        GetTriagePatientsByCriteriaSrv $triagePatientsSrv
    ): JsonResponse {
        $result = $triagePatientsSrv->handle(
            TriagePatientsDto::from($request->validated())
        );

        return (new TriagePatientsResource($result))->response();
    }
}
