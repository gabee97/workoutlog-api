<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\WorkoutExercise;
use App\Models\WorkoutExerciseSet;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WorkoutExerciseSetController extends Controller
{
    public function store(Request $request, WorkoutExercise $workoutExercise)
    {
        $this->authorize('update', $workoutExercise);

        $data = $request->validate([
            'reps' => ['nullable', 'integer', 'min:1'],
            'weight' => ['nullable', 'numeric', 'min:0'],
            'rest_seconds' => ['nullable', 'integer', 'min:0'],
            'rir' => ['nullable', 'integer', 'min:0', 'max:10'],
            'notes' => ['nullable', 'string'],
        ]);

        $nextSetNumber = $workoutExercise->sets()->max('set_number') + 1;

        $data['workout_exercise_id'] = $workoutExercise->id;
        $data['set_number'] = $nextSetNumber;

        $workoutExerciseSet = WorkoutExerciseSet::create($data);

        return ApiResponse::success($workoutExerciseSet, 'Série adicionada com sucesso', 201);
    }

    public function update(Request $request, WorkoutExerciseSet $workoutExerciseSet)
    {
        $this->authorize('update', $workoutExerciseSet);

        $data = $request->validate([
            'reps' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'weight' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'rest_seconds' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'rir' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:10'],
            'notes' => ['sometimes', 'nullable', 'string'],
        ]);

        $workoutExerciseSet->update($data);

        return ApiResponse::success($workoutExerciseSet, 'Série atualizada com sucesso');
    }

    public function destroy(WorkoutExerciseSet $workoutExerciseSet)
    {
        $this->authorize('delete', $workoutExerciseSet);

        $workoutExercise = $workoutExerciseSet->workoutExercise;

        DB::transaction(function () use ($workoutExerciseSet, $workoutExercise) {
            $deletedSetNumber = $workoutExerciseSet->set_number;
            $workoutExerciseSet->delete();

            // Reordenar séries subsequentes
            $workoutExercise->sets()
                ->where('set_number', '>', $deletedSetNumber)
                ->decrement('set_number');
        });

        return ApiResponse::success(null, 'Série removida com sucesso');
    }
}
