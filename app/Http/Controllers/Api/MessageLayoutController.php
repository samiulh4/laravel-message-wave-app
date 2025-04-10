<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MessageLayout;
use Exception;
use App\Functions\ApiResponseFunction;

class MessageLayoutController extends Controller
{
    public function layoutFormList(Request $request)
    {
        try {
            $layouts = MessageLayout::where('is_active', 1)
            ->select('id', 'layout_type', 'layout_name', 'layout_body')
            ->orderBy('id', 'desc')
            ->get();
            // $layouts = $layouts->map(function ($layout) {
            //     return [
            //         'id' => $layout->id,
            //         'layout_name' => $layout->layout_name
            //     ];
            // });
            return ApiResponseFunction::successResponse('Template has been stored successfully.', $layouts, 200);
        } catch (Exception $e) {
            return ApiResponseFunction::errorResponse($e);
        }
    }
}
