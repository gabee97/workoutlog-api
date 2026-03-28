<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class WorkoutController extends Controller
{
    public function index()
    {
        return ApiResponse::success([], 'Lista de treinos');
    }

    public function store(Request $request)
    {
        return ApiResponse::success([], 'Treino criado com sucesso', 201);
    }

    public function show(string $id)
    {
        return ApiResponse::success([], 'Detalhes do treino');
    }

    public function update(Request $request, string $id)
    {
        return ApiResponse::success([], 'Treino atualizado com sucesso');
    }

    public function destroy(string $id)
    {
        return ApiResponse::success([], 'Treino desativado com sucesso');
    }

    public function hide(string $id)
    {
        return ApiResponse::success([], 'Treino ocultado com sucesso');
    }

    public function unhide(string $id)
    {
        return ApiResponse::success([], 'Treino reexibido com sucesso');
    }

    public function clone(string $id)
    {
        return ApiResponse::success([], 'Treino clonado com sucesso');
    }
}
