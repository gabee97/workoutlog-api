<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class WorkoutExerciseSetController extends Controller
{
    public function store(Request $request, string $workoutExercise)
    {
        return ApiResponse::success([], 'Série criada com sucesso', 201);
    }

    public function update(Request $request, string $workoutExerciseSet)
    {
        return ApiResponse::success([], 'Série atualizada com sucesso');
    }

    public function destroy(string $workoutExerciseSet)
    {
        return ApiResponse::success([], 'Série removida com sucesso');
    }
}
