<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\PatientFollowUp\FindPatientsWithoutRecentLogsSrv;
use App\Enums\NotificationType;
use App\Models\User;
use App\Models\UserPatient;
use App\Notifications\AdminFollowUpAlertNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class NotifyPatientsWithoutFollowUpCommand extends Command
{
    protected $signature = 'notifications:patient-follow-up';

    protected $description = 'Notifica a los administradores sobre pacientes sin seguimiento reciente';

    public function handle(FindPatientsWithoutRecentLogsSrv $findPatientsSrv): int
    {
        $days = (int) config('notifications.follow_up_days');
        $entries = $findPatientsSrv->handle($days);
        $administrators = User::role('Administrator')->get();
        $reportedCount = 0;

        foreach ($entries as $entry) {
            /** @var UserPatient $patient */
            $patient = $entry['patient'];

            if ($this->alreadyReportedToday($patient)) {
                continue;
            }

            $administrators->each->notify(new AdminFollowUpAlertNotification($patient, $entry['days_since_last_log']));
            $reportedCount++;
        }

        $this->info("Pacientes reportados a administradores por falta de seguimiento: {$reportedCount}");

        return self::SUCCESS;
    }

    private function alreadyReportedToday(UserPatient $patient): bool
    {
        return DB::table('notifications')
            ->where('type', NotificationType::OverdueGlucoseLog->value)
            ->where('data->patient_id', $patient->id)
            ->whereDate('created_at', today())
            ->exists();
    }
}
