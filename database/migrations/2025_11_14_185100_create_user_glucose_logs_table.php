<?php

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
        Schema::create('user_glucose_logs', function (Blueprint $table) {
            $table->id();
            $table->smallInteger('value');
            $table->tinyInteger('time_block');
            $table->foreignId('user_patient_id');
            $table->foreignId('status_id');
            $table->foreign('user_patient_id')->references('id')->on('user_patients');
            $table->foreign('status_id')->references('id')->on('statuses');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_glucose_logs');
    }
};
