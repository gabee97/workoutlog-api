<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workout_log_exercises', function (Blueprint $table) {

            $table->id();

            $table->foreignId('workout_log_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('exercise_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->unsignedTinyInteger('set_number');

            $table->unsignedSmallInteger('reps')->nullable();

            $table->decimal('weight', 6, 2)->nullable();

            $table->unsignedSmallInteger('rest_seconds')->nullable();

            $table->unsignedTinyInteger('rir')->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index([
                'workout_log_id',
                'exercise_id'
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workout_log_exercises');
    }
};
