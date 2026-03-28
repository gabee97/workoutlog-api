<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class WorkoutExerciseController extends Controller
{
    public function store(Request $request, string $workout)
    {
        return ApiResponse::success([], 'Exercício vinculado ao treino com sucesso', 201);
    }

    public function update(Request $request, string $workoutExercise)
    {
        return ApiResponse::success([], 'Exercício do treino atualizado com sucesso');
    }

    public function destroy(string $workoutExercise)
    {
        return ApiResponse::success([], 'Exercício removido do treino com sucesso');
    }
}
