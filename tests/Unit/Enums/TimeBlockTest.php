<?php

declare(strict_types=1);

namespace Tests\Unit\Enums;

use App\Enums\TimeBlock;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class TimeBlockTest extends TestCase
{
    #[DataProvider('hourProvider')]
    public function test_from_hour_returns_expected_time_block(int $hour, TimeBlock $expected): void
    {
        $this->assertSame($expected, TimeBlock::fromHour($hour));
    }

    public static function hourProvider(): array
    {
        return [
            'inicio de mañana (04:00)' => [4, TimeBlock::FASTING],
            'mediodía (12:00)' => [12, TimeBlock::FASTING],
            'fin de mañana (18:00)' => [18, TimeBlock::FASTING],
            'inicio de anochecer (19:00)' => [19, TimeBlock::NON_FASTING],
            'medianoche (00:00)' => [0, TimeBlock::NON_FASTING],
            'madrugada (03:00)' => [3, TimeBlock::NON_FASTING],
        ];
    }
}
