<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\RequestContactStore;
use App\Functions\ApiResponseFunction;
use Exception;
use App\Models\MessageContact;
use Illuminate\Support\Facades\Auth;
use App\Functions\HelperFunction;
use App\Functions\FileUploadFunction;


class MessageContactController extends Controller
{
    public function contactStore(RequestContactStore $request)
    {
        try {
            $contact_image = $request->file('contact_image')
            ? FileUploadFunction::uploadImageFile($request->file('contact_image'), 'uploads/contacts')
            : null;
            $contact = MessageContact::create([
                'user_id' => HelperFunction::getAuthUserId(),
                'contact_name' => $request->contact_name,
                'contact_email' => $request->contact_email,
                'contact_mobile' => $request->contact_mobile,
                'contact_image' => $contact_image,
            ]);
            $contact->makeHidden(['created_by', 'updated_by']);
            $contact = HelperFunction::dataProcessor($contact);
            return ApiResponseFunction::successResponse('contact has been stored successfully.', $contact, 201);
        } catch (Exception $e) {
            return ApiResponseFunction::errorResponse($e);
        }
    }
}
