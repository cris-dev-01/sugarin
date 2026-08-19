<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Enums\NotificationType;
use App\Models\UserGlucoseLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GlucoseLogRegisteredNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly UserGlucoseLog $log) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function databaseType(object $notifiable): string
    {
        return $this->isAbnormal()
            ? NotificationType::AbnormalGlucoseLog->value
            : NotificationType::GlucoseLog->value;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage())
            ->subject($this->isAbnormal() ? 'Lectura de glucosa fuera de rango' : 'Registro de glucosa exitoso')
            ->greeting("Hola {$notifiable->name},")
            ->line($this->message());

        if ($this->isAbnormal()) {
            $mail->line('Te recomendamos contactar a tu equipo médico si esta situación se repite.');
        }

        return $mail;
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => $this->isAbnormal() ? 'Lectura fuera de rango' : 'Nueva lectura registrada',
            'message' => $this->message(),
        ];
    }

    private function isAbnormal(): bool
    {
        return $this->log->status->name !== 'Rango normal';
    }

    private function message(): string
    {
        return "Se registró una lectura de {$this->log->value} mg/dL ({$this->log->time_block}). Estado: {$this->log->status->name}.";
    }
}
