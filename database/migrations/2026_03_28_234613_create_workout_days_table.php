<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workout_days', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workout_id')->constrained('workouts')->cascadeOnDelete();
            $table->string('name'); // ex: "Treino A", "Push Day"
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workout_days');
    }
};
