<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Exception;
use App\Functions\ApiResponseFunction;
use App\Http\Requests\RequestTemplateStore;

class MessageTemplateController extends Controller
{
    public function templateStore(Request $request)
    {
        try {
           
            return ApiResponseFunction::successResponse('Template has been stored successfully.', [], 201);
        } catch (Exception $e) {
            return ApiResponseFunction::errorResponse($e);
        }
    }
}
