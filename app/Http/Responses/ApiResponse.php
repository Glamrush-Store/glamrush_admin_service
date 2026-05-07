<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;

final class ApiResponse
{
    public static function success($data, string $message = 'Success', int $code = 200)
    {
        if ($data instanceof \Illuminate\Http\Resources\Json\AnonymousResourceCollection) {
            $response = $data->response()->getData(true);

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $response['data'],
                'meta' => $response['meta'] ?? null,
                'links' => $response['links'] ?? null,
            ], $code);
        } else {
            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $data,
            ], $code);
        }
    }

    public static function error(
        string $message,
        array $errors = [],
        int $status = 400
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => null,
            'errors' => $errors,
        ], $status);
    }
}
