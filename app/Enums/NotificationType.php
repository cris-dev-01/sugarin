<?php

declare(strict_types=1);

namespace App\Enums;

enum NotificationType: string
{
    case GlucoseLog = 'glucose-log';
    case AbnormalGlucoseLog = 'abnormal-glucose-log';
    case OverdueGlucoseLog = 'overdue-glucose-log';
    case ProfileChanged = 'profile-changed';
}
