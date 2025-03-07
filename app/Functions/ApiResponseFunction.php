<?php

namespace App\Functions;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Exceptions\HttpResponseException;


class ApiResponseFunction
{
    public static function errorResponse($e, $code = 500)
    {
        $response = [];
        $response['success'] = false;
        $response['data'] = [];
        if ($e instanceof Exception) {
            Log::error('Exception occurred', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            $response['message'] = 'Something went wrong! Process not completed';
        } elseif (is_string($e)) {
            $response['message'] = $e;
        }
        return response()->json($response, $code);
    }

    public static function successResponse($message, $data = [], $code = 200)
    {
        $response = [];
        $response['success'] = true;
        $response['data'] = $data;
        $response['message'] = $message;
        return response()->json($response, $code);
    }
}
