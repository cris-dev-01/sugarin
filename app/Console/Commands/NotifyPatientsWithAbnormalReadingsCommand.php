<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\PatientFollowUp\FindPatientsWithAbnormalReadingsSrv;
use App\Enums\NotificationType;
use App\Models\User;
use App\Models\UserPatient;
use App\Notifications\AdminAbnormalReadingAlertNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class NotifyPatientsWithAbnormalReadingsCommand extends Command
{
    protected $signature = 'notifications:patient-abnormal-readings';

    protected $description = 'Notifica a los administradores sobre pacientes con lecturas de glucosa fuera de rango recientes';

    public function handle(FindPatientsWithAbnormalReadingsSrv $findPatientsSrv): int
    {
        $windowHours = (int) config('notifications.abnormal_reading_window_hours');
        $entries = $findPatientsSrv->handle($windowHours);
        $administrators = User::role('Administrator')->get();
        $reportedCount = 0;

        foreach ($entries as $entry) {
            /** @var UserPatient $patient */
            $patient = $entry['patient'];

            if ($this->alreadyReportedToday($patient)) {
                continue;
            }

            $administrators->each->notify(new AdminAbnormalReadingAlertNotification($patient, $entry['abnormal_status_names']));
            $reportedCount++;
        }

        $this->info("Pacientes reportados a administradores por lecturas alteradas: {$reportedCount}");

        return self::SUCCESS;
    }

    private function alreadyReportedToday(UserPatient $patient): bool
    {
        return DB::table('notifications')
            ->where('type', NotificationType::AbnormalGlucoseLog->value)
            ->where('data->patient_id', $patient->id)
            ->whereDate('created_at', today())
            ->exists();
    }
}
