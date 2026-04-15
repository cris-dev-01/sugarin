<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class PatientSummaryController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('PatientDashboard/Index');
    }
}
