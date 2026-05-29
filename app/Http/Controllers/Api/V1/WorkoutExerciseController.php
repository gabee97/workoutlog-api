<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\WorkoutDay;
use App\Models\WorkoutExercise;
use App\Models\Exercise;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class WorkoutExerciseController extends Controller
{
    public function store(Request $request, WorkoutDay $workoutDay)
    {
        $this->authorize('update', $workoutDay);

        $data = $request->validate([
            'exercise_id' => ['required', 'exists:exercises,id'],
            'target_sets' => ['nullable', 'integer', 'min:1'],
            'min_reps' => ['nullable', 'integer', 'min:1'],
            'max_reps' => ['nullable', 'integer', 'min:1'],
            'rest_seconds' => ['nullable', 'integer', 'min:0'],
            'notes' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        // Verificar se o exercício é visível para o usuário
        $userId = $request->user()->id;
        $exercise = Exercise::visibleTo($userId)->find($data['exercise_id']);
        
        if (!$exercise) {
            return ApiResponse::error('Exercício inválido ou não permitido.', [], 403);
        }

        $data['workout_day_id'] = $workoutDay->id;
        $workoutExercise = WorkoutExercise::create($data);

        return ApiResponse::success($workoutExercise, 'Exercício adicionado ao dia de treino com sucesso', 201);
    }

    public function update(Request $request, WorkoutExercise $workoutExercise)
    {
        $this->authorize('update', $workoutExercise);

        $data = $request->validate([
            'target_sets' => ['sometimes', 'required', 'integer', 'min:1'],
            'min_reps' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'max_reps' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'rest_seconds' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'notes' => ['sometimes', 'nullable', 'string'],
            'sort_order' => ['sometimes', 'nullable', 'integer', 'min:0'],
        ]);

        $workoutExercise->update($data);

        return ApiResponse::success($workoutExercise->load(['exercise', 'sets']), 'Configuração do exercício atualizada com sucesso');
    }

    public function destroy(WorkoutExercise $workoutExercise)
    {
        $this->authorize('delete', $workoutExercise);

        $workoutExercise->delete();

        return ApiResponse::success(null, 'Exercício removido do dia de treino com sucesso');
    }
}
