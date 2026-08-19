<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Enums\NotificationType;
use App\Models\UserPatient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminAbnormalReadingAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param list<string> $abnormalStatusNames
     */
    public function __construct(
        private readonly UserPatient $patient,
        private readonly array $abnormalStatusNames,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function databaseType(object $notifiable): string
    {
        return NotificationType::AbnormalGlucoseLog->value;
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject('Alerta: paciente con lecturas de glucosa fuera de rango')
            ->greeting("Hola {$notifiable->name},")
            ->line($this->message());
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Paciente con lecturas fuera de rango',
            'message' => $this->message(),
            'patient_id' => $this->patient->id,
        ];
    }

    private function message(): string
    {
        $patientName = $this->patient->user?->name ?? 'Un paciente';
        $statuses = implode(' y ', $this->abnormalStatusNames);

        return "{$patientName} registró lecturas de glucosa con status: {$statuses} en las últimas horas.";
    }
}
