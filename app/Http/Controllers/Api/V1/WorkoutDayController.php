<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Workout;
use App\Models\WorkoutDay;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class WorkoutDayController extends Controller
{
    public function store(Request $request, Workout $workout)
    {
        $this->authorize('update', $workout);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $data['workout_id'] = $workout->id;
        $day = WorkoutDay::create($data);

        return ApiResponse::success($day, 'Dia de treino criado com sucesso', 201);
    }

    public function update(Request $request, WorkoutDay $workoutDay)
    {
        $this->authorize('update', $workoutDay);

        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'sort_order' => ['sometimes', 'nullable', 'integer', 'min:0'],
        ]);

        $workoutDay->update($data);

        return ApiResponse::success($workoutDay, 'Dia de treino atualizado com sucesso');
    }

    public function destroy(WorkoutDay $workoutDay)
    {
        $this->authorize('delete', $workoutDay);

        $workoutDay->delete();

        return ApiResponse::success(null, 'Dia de treino removido com sucesso');
    }
}
