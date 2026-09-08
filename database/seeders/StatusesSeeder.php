<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Status;
use Illuminate\Database\Seeder;

class StatusesSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            ['name' => 'Rango normal', 'order' => 1],
            ['name' => 'Elevado - fuera de rango normal', 'order' => 2],
            ['name' => 'Bajo - fuera de rango normal', 'order' => 3],
        ];

        foreach ($statuses as $status) {
            Status::firstOrCreate(['name' => $status['name']], $status);
        }
    }
}
