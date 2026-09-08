<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\GlucoseDashboard\GetPatientSummarySrv;
use App\DataTransferObjects\GlucoseDashboard\PatientSummaryDto;
use App\Http\Requests\GlucoseDashboard\PatientSummaryRequest;
use Inertia\Inertia;
use Inertia\Response;

class PatientSummaryController extends Controller
{
    public function index(PatientSummaryRequest $request, GetPatientSummarySrv $summarySrv): Response
    {
        $patient = $request->user()->patient;

        $summary = $patient
            ? $summarySrv->handle(
                PatientSummaryDto::from([
                    ...$request->validated(),
                    'user_patient_id' => $patient->id,
                ])
            )
            : null;

        return Inertia::render('PatientDashboard/Index', [
            'summary' => $summary,
        ]);
    }
}
