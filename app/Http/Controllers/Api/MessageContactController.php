<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\RequestContactStore;
use App\Http\Requests\RequestContactUpdate;
use App\Functions\ApiResponseFunction;
use Exception;
use App\Models\MessageContact;
use Illuminate\Support\Facades\Auth;
use App\Functions\HelperFunction;
use App\Functions\FileUploadFunction;
use App\Functions\EncryptionFunction;


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
            return ApiResponseFunction::successResponse('Contact has been stored successfully.', $contact, 201);
        } catch (Exception $e) {
            return ApiResponseFunction::errorResponse($e);
        }
    }

    public function contactList(Request $request)
    {
        try {
            $authUserId = HelperFunction::getAuthUserId();

            $page = $request->has('page') ? $request->get('page') : 1;
            $limit = $request->has('limit') ? $request->get('limit') : 5;
            $search = $request->has('search') ? $request->get('search') : '';

            $offset = ($page - 1) * $limit;

            $query = MessageContact::where('user_id', $authUserId);
            if (!empty($search)) {
                $query->where(function ($query) use ($search) {
                    $query->where('contact_name', 'like', "%$search%")
                        ->orWhere('contact_email', 'like', "%$search%")
                        ->orWhere('contact_mobile', 'like', "%$search%");
                });
            }

            $contacts = $query->orderBy('updated_at', 'desc')
                ->limit($limit)
                ->offset($offset)
                ->get()
                ->map(function ($contact) {
                    $contact->makeHidden(['created_by', 'updated_by']);
                    return HelperFunction::dataProcessor($contact);
                });

            return ApiResponseFunction::successResponse('Contact list fetch successfully.', $contacts, 200);
        } catch (Exception $e) {
            return ApiResponseFunction::errorResponse($e);
        }
    }

    public function contactDetail($encodedId)
    {
        try {
            $decodedId = EncryptionFunction::decodeId($encodedId);
            
            $contact = MessageContact::find($decodedId);
            if (!$contact) {
                return ApiResponseFunction::errorResponse('Contact not found!', 404);
            }
            $contact->makeHidden(['created_by', 'updated_by']);
            $contact = HelperFunction::dataProcessor($contact);
            
            return ApiResponseFunction::successResponse('Contact details fetch successfully.', $contact, 200);
        } catch (Exception $e) {
            return ApiResponseFunction::errorResponse($e);
        }
    }

    public function contactUpdate($encodedId, RequestContactUpdate $request)
    {
        
        try {
            
            $decodedId = EncryptionFunction::decodeId($encodedId);
            
            $contact = MessageContact::find($decodedId);
            if (!$contact) {
                return ApiResponseFunction::errorResponse('Contact not found!', 404);
            }

            $contact->contact_name = $request->contact_name;
            $contact->contact_email = $request->contact_email;
            $contact->contact_mobile = $request->contact_mobile;
            $contact->is_active = $request->is_active;

            $contact_image = $request->file('contact_image')
                ? FileUploadFunction::uploadImageFile($request->file('contact_image'), 'uploads/contacts')
                : null;

            $contact->contact_image = $contact_image ? $contact_image : $contact->contact_image;

            $contact->save();

            $contact->makeHidden(['created_by', 'updated_by']);
            $contact = HelperFunction::dataProcessor($contact);

            return ApiResponseFunction::successResponse('Contact has been updated successfully.', $contact, 200);
        } catch (Exception $e) {
            return ApiResponseFunction::errorResponse($e);
        }
    }
}
