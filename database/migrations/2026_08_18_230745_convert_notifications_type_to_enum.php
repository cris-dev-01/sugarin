<?php

use App\Enums\NotificationType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Datos de desarrollo únicamente (la app aún no está en producción): se
        // truncan porque los registros existentes traen el nombre de clase PHP
        // como `type`, incompatible con los valores del nuevo ENUM.
        DB::table('notifications')->truncate();

        $values = implode(',', array_map(
            fn (NotificationType $case) => "'{$case->value}'",
            NotificationType::cases()
        ));

        DB::statement("ALTER TABLE notifications MODIFY type ENUM({$values}) NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE notifications MODIFY type VARCHAR(255) NOT NULL');
    }
};
