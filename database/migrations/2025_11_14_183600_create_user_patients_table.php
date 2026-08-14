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
        Schema::create('user_patients', function (Blueprint $table) {
            $table->id();
            $table->enum('document_type', ['rut']);
            $table->string('document', 20);
            $table->date('illness_found_at');
            $table->smallInteger('initial_max_glucose_value');
            $table->foreignId('user_id');
            $table->foreignId('glucose_range_id');
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('glucose_range_id')->references('id')->on('glucose_ranges');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_patients');
    }
};
