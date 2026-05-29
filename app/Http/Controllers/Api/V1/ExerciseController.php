<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Exercise;
use App\Models\MuscleGroup;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ExerciseController extends Controller
{
    public function index(Request $request)
    {
        $userId = (int) $request->user()->id;

        $hiddenIds = DB::table('user_hidden_exercises')
            ->where('user_id', $userId)
            ->pluck('exercise_id')
            ->all();

        $q = Exercise::query()
            ->visibleTo($userId)
            ->whereNotIn('id', $hiddenIds)
            ->where('is_active', true);

        if ($request->filled('muscle_group_id')) {
            $q->where('muscle_group_id', (int) $request->input('muscle_group_id'));
        }

        if ($request->filled('equipment')) {
            $q->where('equipment', $request->input('equipment'));
        }

        $items = $q->orderBy('sort_order')->orderBy('name')->get();

        return ApiResponse::success($items, 'Exercícios listados com sucesso');
    }

    public function store(Request $request)
    {
        $userId = (int) $request->user()->id;

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255',
                Rule::unique('exercises', 'name')->where(fn ($q) => $q->where('owner_user_id', $userId)),
            ],
            'muscle_group_id' => ['required', 'integer', 'exists:muscle_groups,id'],
            'equipment' => ['nullable', 'string', 'max:255'],
            'level' => ['nullable', 'integer', 'min:1', 'max:5'],
            'instructions' => ['nullable', 'string'],
            'video_url' => ['nullable', 'url', 'max:2048'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        // Validar se o muscle_group é visível (global ou do user) e não está oculto
        $mg = MuscleGroup::query()
            ->where('id', (int) $data['muscle_group_id'])
            ->visibleTo($userId)
            ->first();

        if (! $mg) {
            return ApiResponse::error('Categoria muscular inválida para este usuário.', [], 403);
        }

        $hiddenMg = DB::table('user_hidden_muscle_groups')
            ->where('user_id', $userId)
            ->where('muscle_group_id', (int) $data['muscle_group_id'])
            ->exists();

        if ($hiddenMg) {
            return ApiResponse::error('Esta categoria muscular está ocultada para você. Reexiba-a antes de usar.', [], 422);
        }

        $data['owner_user_id'] = $userId;
        $data['slug'] = Str::slug($data['name']);
        $data['is_active'] = true;

        // slug único por owner
        $baseSlug = $data['slug'];
        $i = 2;
        while (Exercise::where('owner_user_id', $userId)->where('slug', $data['slug'])->exists()) {
            $data['slug'] = "{$baseSlug}-{$i}";
            $i++;
        }

        $item = Exercise::create($data);

        return ApiResponse::success($item, 'Exercício criado com sucesso', 201);
    }

    public function show(Request $request, Exercise $exercise)
    {
        $this->authorize('view', $exercise);

        $userId = (int) $request->user()->id;
        $isHidden = DB::table('user_hidden_exercises')
            ->where('user_id', $userId)
            ->where('exercise_id', $exercise->id)
            ->exists();

        if (! $exercise->is_active || $isHidden) {
            return ApiResponse::error('Exercício não encontrado.', [], 404);
        }

        return ApiResponse::success(
            $exercise->load('muscleGroup'),
            'Detalhes do exercício'
        );
    }

    public function update(Request $request, Exercise $exercise)
    {
        $this->authorize('update', $exercise);

        $userId = (int) $request->user()->id;

        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255',
                Rule::unique('exercises', 'name')
                    ->where(fn ($q) => $q->where('owner_user_id', $userId))
                    ->ignore($exercise->id),
            ],
            'muscle_group_id' => ['sometimes', 'required', 'integer', 'exists:muscle_groups,id'],
            'equipment' => ['sometimes', 'nullable', 'string', 'max:255'],
            'level' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:5'],
            'instructions' => ['sometimes', 'nullable', 'string'],
            'video_url' => ['sometimes', 'nullable', 'url', 'max:2048'],
            'sort_order' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        if (isset($data['muscle_group_id'])) {
            $mg = MuscleGroup::query()
                ->where('id', (int) $data['muscle_group_id'])
                ->visibleTo($userId)
                ->first();

            if (! $mg) {
                return ApiResponse::error('Categoria muscular inválida para este usuário.', [], 403);
            }
        }

        if (isset($data['name'])) {
            $newSlug = Str::slug($data['name']);
            $baseSlug = $newSlug;
            $i = 2;

            while (
                Exercise::where('owner_user_id', $userId)
                    ->where('slug', $newSlug)
                    ->where('id', '!=', $exercise->id)
                    ->exists()
            ) {
                $newSlug = "{$baseSlug}-{$i}";
                $i++;
            }

            $data['slug'] = $newSlug;
        }

        $exercise->update($data);

        return ApiResponse::success($exercise->fresh(), 'Exercício atualizado com sucesso');
    }

    public function destroy(Request $request, Exercise $exercise)
    {
        $this->authorize('delete', $exercise);

        $exercise->update(['is_active' => false]);

        return ApiResponse::success(null, 'Exercício desativado com sucesso');
    }

    public function hide(Request $request, Exercise $exercise)
    {
        $userId = (int) $request->user()->id;

        if ((int) $exercise->owner_user_id !== 0) {
            return ApiResponse::error('Você só pode ocultar exercícios padrão (globais).', [], 403);
        }

        DB::table('user_hidden_exercises')->updateOrInsert(
            ['user_id' => $userId, 'exercise_id' => $exercise->id],
            ['created_at' => now(), 'updated_at' => now()]
        );

        return ApiResponse::success(null, 'Exercício ocultado com sucesso');
    }

    public function unhide(Request $request, Exercise $exercise)
    {
        $userId = (int) $request->user()->id;

        DB::table('user_hidden_exercises')
            ->where('user_id', $userId)
            ->where('exercise_id', $exercise->id)
            ->delete();

        return ApiResponse::success(null, 'Exercício reexibido com sucesso');
    }
}
