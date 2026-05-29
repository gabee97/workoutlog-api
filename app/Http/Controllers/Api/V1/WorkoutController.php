<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Workout;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class WorkoutController extends Controller
{
    public function index(Request $request)
    {
        $userId = (int) $request->user()->id;

        $hiddenIds = DB::table('user_hidden_workouts')
            ->where('user_id', $userId)
            ->pluck('workout_id')
            ->all();

        $items = Workout::query()
            ->visibleTo($userId)
            ->whereNotIn('id', $hiddenIds)
            ->where('is_active', true)
            ->with(['workoutDays.workoutExercises.exercise', 'workoutDays.workoutExercises.sets'])
            ->orderBy('sort_order')
            ->get();

        return ApiResponse::success($items, 'Treinos listados com sucesso');
    }

    public function store(Request $request)
    {
        $userId = (int) $request->user()->id;

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255',
                Rule::unique('workouts', 'name')->where(fn ($q) => $q->where('owner_user_id', $userId)),
            ],
            'description' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $data['owner_user_id'] = $userId;
        $data['slug'] = Str::slug($data['name']);

        // Slug único por usuário
        $baseSlug = $data['slug'];
        $i = 2;
        while (Workout::where('owner_user_id', $userId)->where('slug', $data['slug'])->exists()) {
            $data['slug'] = "{$baseSlug}-{$i}";
            $i++;
        }

        $item = Workout::create($data);

        return ApiResponse::success($item, 'Treino criado com sucesso', 201);
    }

    public function show(Workout $workout)
    {
        $this->authorize('view', $workout);

        $workout->load(['workoutDays.workoutExercises.exercise', 'workoutDays.workoutExercises.sets']);

        return ApiResponse::success($workout, 'Detalhes do treino');
    }

    public function update(Request $request, Workout $workout)
    {
        $this->authorize('update', $workout);

        $userId = (int) $request->user()->id;

        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255',
                Rule::unique('workouts', 'name')
                    ->where(fn ($q) => $q->where('owner_user_id', $userId))
                    ->ignore($workout->id),
            ],
            'description' => ['sometimes', 'nullable', 'string'],
            'sort_order' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        if (isset($data['name'])) {
            $newSlug = Str::slug($data['name']);
            $baseSlug = $newSlug;
            $i = 2;
            while (Workout::where('owner_user_id', $userId)->where('slug', $newSlug)->where('id', '!=', $workout->id)->exists()) {
                $newSlug = "{$baseSlug}-{$i}";
                $i++;
            }
            $data['slug'] = $newSlug;
        }

        $workout->update($data);

        return ApiResponse::success($workout->fresh(), 'Treino atualizado com sucesso');
    }

    public function destroy(Workout $workout)
    {
        $this->authorize('delete', $workout);

        $workout->update(['is_active' => false]);

        return ApiResponse::success(null, 'Treino desativado com sucesso');
    }

    public function hide(Request $request, Workout $workout)
    {
        $userId = (int) $request->user()->id;

        if ((int) $workout->owner_user_id !== 0) {
            return ApiResponse::error('Você só pode ocultar treinos padrão (globais).', [], 403);
        }

        DB::table('user_hidden_workouts')->updateOrInsert(
            ['user_id' => $userId, 'workout_id' => $workout->id],
            ['created_at' => now(), 'updated_at' => now()]
        );

        return ApiResponse::success(null, 'Treino ocultado com sucesso');
    }

    public function unhide(Request $request, Workout $workout)
    {
        $userId = (int) $request->user()->id;

        DB::table('user_hidden_workouts')
            ->where('user_id', $userId)
            ->where('workout_id', $workout->id)
            ->delete();

        return ApiResponse::success(null, 'Treino reexibido com sucesso');
    }

    public function clone(Request $request, Workout $workout)
    {
        $this->authorize('view', $workout);

        $userId = (int) $request->user()->id;

        $isHidden = DB::table('user_hidden_workouts')
            ->where('user_id', $userId)
            ->where('workout_id', $workout->id)
            ->exists();

        if (! $workout->is_active || $isHidden) {
            return ApiResponse::error('Treino não encontrado.', [], 404);
        }

        $workout->load('workoutDays.workoutExercises.sets');

        // Clone do treino
        $newWorkout = $workout->replicate();
        $newWorkout->owner_user_id = $userId;
        $newWorkout->name = $workout->name.' (Cópia)';
        $newWorkout->slug = Str::slug($newWorkout->name);

        // Garantir slug único
        $baseSlug = $newWorkout->slug;
        $i = 2;
        while (Workout::where('owner_user_id', $userId)->where('slug', $newWorkout->slug)->exists()) {
            $newWorkout->slug = "{$baseSlug}-{$i}";
            $i++;
        }

        $newWorkout->save();

        // Clone dos dias e exercícios
        foreach ($workout->workoutDays as $day) {
            $newDay = $day->replicate();
            $newDay->workout_id = $newWorkout->id;
            $newDay->save();

            foreach ($day->workoutExercises as $exercise) {
                $newEx = $exercise->replicate();
                $newEx->workout_day_id = $newDay->id;
                $newEx->save();

                foreach ($exercise->sets as $set) {
                    $newSet = $set->replicate();
                    $newSet->workout_exercise_id = $newEx->id;
                    $newSet->save();
                }
            }
        }

        return ApiResponse::success($newWorkout->load('workoutDays.workoutExercises.sets'), 'Treino clonado com sucesso', 201);
    }
}
