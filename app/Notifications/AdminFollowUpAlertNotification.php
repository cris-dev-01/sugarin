<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Enums\NotificationType;
use App\Models\UserPatient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminFollowUpAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly UserPatient $patient,
        private readonly ?int $daysSinceLastLog,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function databaseType(object $notifiable): string
    {
        return NotificationType::OverdueGlucoseLog->value;
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject('Alerta: paciente sin seguimiento')
            ->greeting("Hola {$notifiable->name},")
            ->line($this->message());
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Paciente sin seguimiento',
            'message' => $this->message(),
            'patient_id' => $this->patient->id,
        ];
    }

    private function message(): string
    {
        $patientName = $this->patient->user?->name ?? 'Un paciente';

        return is_null($this->daysSinceLastLog)
            ? "{$patientName} no tiene ningún registro de glucosa."
            : "{$patientName} no registra una lectura de glucosa hace {$this->daysSinceLastLog} día(s).";
    }
}
