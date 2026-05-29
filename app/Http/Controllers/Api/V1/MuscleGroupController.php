<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\MuscleGroup;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class MuscleGroupController extends Controller
{
    public function index(Request $request)
    {
        $userId = (int) $request->user()->id;

        $hiddenIds = DB::table('user_hidden_muscle_groups')
            ->where('user_id', $userId)
            ->pluck('muscle_group_id')
            ->all();

        $items = MuscleGroup::query()
            ->visibleTo($userId)
            ->whereNotIn('id', $hiddenIds)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return ApiResponse::success($items, 'Categorias musculares listadas com sucesso');
    }

    public function store(Request $request)
    {
        $userId = (int) $request->user()->id;

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255',
                Rule::unique('muscle_groups', 'name')->where(fn ($q) => $q->where('owner_user_id', $userId)),
            ],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $data['owner_user_id'] = $userId;
        $data['slug'] = Str::slug($data['name']);
        $data['is_active'] = true;

        // slug único por owner (se já existir, adiciona sufixo)
        $baseSlug = $data['slug'];
        $i = 2;
        while (MuscleGroup::where('owner_user_id', $userId)->where('slug', $data['slug'])->exists()) {
            $data['slug'] = "{$baseSlug}-{$i}";
            $i++;
        }

        $item = MuscleGroup::create($data);

        return ApiResponse::success($item, 'Categoria muscular criada com sucesso', 201);
    }

    public function show(Request $request, MuscleGroup $muscleGroup)
    {
        $this->authorize('view', $muscleGroup);

        $userId = (int) $request->user()->id;
        $isHidden = DB::table('user_hidden_muscle_groups')
            ->where('user_id', $userId)
            ->where('muscle_group_id', $muscleGroup->id)
            ->exists();

        if (! $muscleGroup->is_active || $isHidden) {
            return ApiResponse::error('Categoria muscular não encontrada.', [], 404);
        }

        return ApiResponse::success($muscleGroup, 'Detalhes da categoria muscular');
    }

    public function update(Request $request, MuscleGroup $muscleGroup)
    {
        $this->authorize('update', $muscleGroup);

        $userId = (int) $request->user()->id;

        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255',
                Rule::unique('muscle_groups', 'name')
                    ->where(fn ($q) => $q->where('owner_user_id', $userId))
                    ->ignore($muscleGroup->id),
            ],
            'sort_order' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        if (isset($data['name'])) {
            $newSlug = Str::slug($data['name']);

            $baseSlug = $newSlug;
            $i = 2;
            while (
                MuscleGroup::where('owner_user_id', $userId)
                    ->where('slug', $newSlug)
                    ->where('id', '!=', $muscleGroup->id)
                    ->exists()
            ) {
                $newSlug = "{$baseSlug}-{$i}";
                $i++;
            }

            $data['slug'] = $newSlug;
        }

        $muscleGroup->update($data);

        return ApiResponse::success($muscleGroup->fresh(), 'Categoria muscular atualizada com sucesso');
    }

    public function destroy(Request $request, MuscleGroup $muscleGroup)
    {
        $this->authorize('delete', $muscleGroup);

        $muscleGroup->update(['is_active' => false]);

        return ApiResponse::success(null, 'Categoria muscular desativada com sucesso');
    }

    public function hide(Request $request, MuscleGroup $muscleGroup)
    {
        $userId = (int) $request->user()->id;

        // Só faz sentido ocultar defaults (owner 0). Se quiser permitir ocultar do usuário também, remove isso.
        if ((int) $muscleGroup->owner_user_id !== 0) {
            return ApiResponse::error('Você só pode ocultar categorias padrão (globais).', [], 403);
        }

        DB::table('user_hidden_muscle_groups')->updateOrInsert(
            ['user_id' => $userId, 'muscle_group_id' => $muscleGroup->id],
            ['created_at' => now(), 'updated_at' => now()]
        );

        return ApiResponse::success(null, 'Categoria muscular ocultada com sucesso');
    }

    public function unhide(Request $request, MuscleGroup $muscleGroup)
    {
        $userId = (int) $request->user()->id;

        DB::table('user_hidden_muscle_groups')
            ->where('user_id', $userId)
            ->where('muscle_group_id', $muscleGroup->id)
            ->delete();

        return ApiResponse::success(null, 'Categoria muscular reexibida com sucesso');
    }
}
