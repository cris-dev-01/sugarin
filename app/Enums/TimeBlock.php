<?php

declare(strict_types=1);

namespace App\Enums;

enum TimeBlock: string
{
    case FASTING = 'mañana';
    case NON_FASTING = 'anochecer';

    public static function fromHour(int $hour): self
    {
        return ($hour >= 4 && $hour <= 18)
            ? self::FASTING
            : self::NON_FASTING;
    }
}
