<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    public static function success(
        mixed $data = null,
        string $message = 'Operação realizada com sucesso',
        int $status = 200,
        array $headers = []
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'data' => $data ?? (object) [],
            'message' => $message,
        ], $status, $headers);
    }

    public static function error(
        string $message = 'Ocorreu um erro',
        array $errors = [],
        int $status = 400,
        array $headers = []
    ): JsonResponse {
        $payload = [
            'success' => false,
            'message' => $message,
        ];

        // Só inclui "errors" quando tiver algo (evita poluir respostas como 404/500)
        if (!empty($errors)) {
            $payload['errors'] = $errors;
        }

        return response()->json($payload, $status, $headers);
    }
}
