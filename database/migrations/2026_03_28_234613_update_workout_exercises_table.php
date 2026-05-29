<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workout_exercises', function (Blueprint $table) {
            // Remove a ligação direta com o workout (será via workout_day)
            $table->dropConstrainedForeignId('workout_id');

            // Nova estrutura
            $table->foreignId('workout_day_id')->after('id')->constrained('workout_days')->cascadeOnDelete();
            
            // Campos de configuração
            $table->integer('target_sets')->default(3)->after('exercise_id');
            $table->integer('min_reps')->nullable()->after('target_sets');
            $table->integer('max_reps')->nullable()->after('min_reps');
            $table->integer('rest_seconds')->default(60)->after('max_reps');
        });
    }

    public function down(): void
    {
        Schema::table('workout_exercises', function (Blueprint $table) {
            $table->dropConstrainedForeignId('workout_day_id');
            $table->dropColumn(['target_sets', 'min_reps', 'max_reps', 'rest_seconds']);
            
            $table->foreignId('workout_id')->after('id')->constrained('workouts')->cascadeOnDelete();
        });
    }
};
