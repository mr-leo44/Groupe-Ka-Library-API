<?php
namespace App\Helpers;

class ApiResponse
{
    public static function success(string $message = '', $data = '', int $status = 200)
    {
        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => $message,
        ], $status);
    }

    public static function error(string $message = '', $errors = null, int $status = 400)
    {
        return response()->json([
            'success' => false,
            'errors' => $errors,
            'message' => $message,
        ], $status);
    }
}
