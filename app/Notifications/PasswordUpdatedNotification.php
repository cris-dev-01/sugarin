<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Enums\NotificationType;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class PasswordUpdatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function databaseType(object $notifiable): string
    {
        return NotificationType::ProfileChanged->value;
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Contraseña actualizada',
            'message' => 'Tu contraseña fue actualizada correctamente.',
        ];
    }
}
