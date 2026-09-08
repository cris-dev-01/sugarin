<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('glucose_ranges', function (Blueprint $table) {
            $table->id();
            $table->string('alias', 50)->unique();
            $table->smallInteger('min_fasting_value');
            $table->smallInteger('max_fasting_value');
            $table->smallInteger('min_non_fasting_value');
            $table->smallInteger('max_non_fasting_value');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('glucose_ranges');
    }
};
