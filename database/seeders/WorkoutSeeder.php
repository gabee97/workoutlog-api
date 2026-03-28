<?php

namespace Database\Seeders;

use App\Models\Workout;
use App\Models\WorkoutExercise;
use App\Models\WorkoutExerciseSet;
use App\Models\Exercise;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class WorkoutSeeder extends Seeder
{
    public function run(): void
    {
        // ===== TREINO DEFAULT =====
        $workout = Workout::create([
            'owner_user_id' => 0,
            'name'          => 'Treino A - Peito e Tríceps',
            'slug'          => Str::slug('Treino A - Peito e Tríceps'),
            'description'   => 'Treino básico para iniciantes focado em peito e tríceps.',
            'sort_order'    => 1,
        ]);

        // ===== EXERCÍCIOS DO TREINO =====
        $exerciseNames = [
            'Supino reto com barra',
            'Tríceps testa',
        ];

        foreach ($exerciseNames as $exerciseIndex => $exerciseName) {

            $exercise = Exercise::where('name', $exerciseName)
                ->where('owner_user_id', 0)
                ->first();

            if (!$exercise) {
                continue;
            }

            $workoutExercise = WorkoutExercise::create([
                'workout_id'  => $workout->id,
                'exercise_id' => $exercise->id,
                'sort_order'  => $exerciseIndex + 1,
            ]);

            // ===== SÉRIES =====
            for ($set = 1; $set <= 3; $set++) {
                WorkoutExerciseSet::create([
                    'workout_exercise_id' => $workoutExercise->id,
                    'set_number'          => $set,
                    'reps'                => 10,
                    'weight'              => null, // carga livre
                    'rest_seconds'        => 60,
                ]);
            }
        }
    }
}
