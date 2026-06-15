<?php

declare(strict_types=1);

namespace App\Actions\Patients;

use App\Models\UserPatient;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;
use Throwable;

class DeletePatientSrv
{
    use AsAction;

    /**
     * @throws Throwable
     */
    public function handle(UserPatient $patient): UserPatient
    {
        DB::transaction(function () use ($patient) {
            $patient->user()->delete();
            $patient->delete();
        });

        return $patient;
    }
}
