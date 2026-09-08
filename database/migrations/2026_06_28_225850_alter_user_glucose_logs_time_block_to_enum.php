<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('DELETE FROM user_glucose_logs');

        DB::statement("ALTER TABLE user_glucose_logs MODIFY COLUMN time_block ENUM('mañana', 'anochecer') NOT NULL");
    }

    public function down(): void
    {
        DB::statement('DELETE FROM user_glucose_logs');

        DB::statement('ALTER TABLE user_glucose_logs MODIFY COLUMN time_block TINYINT NOT NULL');
    }
};
