2025_11_14_173000_create_user_patients_table<?php

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
        Schema::create('glucose_range', function (Blueprint $table) {
            $table->id();
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
        Schema::dropIfExists('glucose_range');
    }
};
