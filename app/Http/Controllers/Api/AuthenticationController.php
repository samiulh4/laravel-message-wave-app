<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Exception;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\RequestSignUp;
use App\Http\Requests\RequestAuthUserUpdate;
use App\Functions\ApiResponseFunction;
use App\Functions\EncryptionFunction;
use App\Functions\HelperFunction;
use App\Functions\FileUploadFunction;

class AuthenticationController extends Controller
{
    public function signUp(RequestSignUp $request)
    {
        try {
            $avatar = $request->file('avatar')
                ? FileUploadFunction::uploadFile($request->file('avatar'), 'uploads/avatars')
                : null;

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'username' => $request->email,
                'gender_code' => $request->gender_code,
                'password' => Hash::make($request->password),
                'avatar' => $avatar,
            ]);

            $data = [
                'token' => $user->createToken('MessageApp')->plainTextToken,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar,
                'gender_code' => $user->gender_code,
            ];

            return successResponse('User registered successfully.', $data, 201);
        } catch (Exception $e) {
            return errorResponse($e);
        }
    }


    public function signIn(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return ApiResponseFunction::errorResponse('The provided credentials are incorrect.', 404);
        }

        $token = $user->createToken('AuthToken')->plainTextToken;
        $data = [];
        $data['token'] = $token;
        $data['name'] = $user->name;
        $data['email'] = $user->email;
        $data['username'] = $user->username;
        $data['gender_code'] = $user->gender_code;
        $data['avatar'] = $user->avatar;
        $data['user_type_code'] = $user->user_type_code;


        return ApiResponseFunction::successResponse('User sign in successfully.', $data, 200);
    }

    public function getAuthUserData(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return ApiResponseFunction::errorResponse('User not found!', 404);
        }
        $user->makeHidden(['created_by', 'updated_by', 'email_verified_at', 'is_active']);
        $user = HelperFunction::dataProcessor($user);
        return ApiResponseFunction::successResponse('User data retrieved successfully.', $user, 200);
    }

    public function signOut(Request $request)
    {
        try {
            // Revoke the token that was used to authenticate the request
            $request->user()->currentAccessToken()->delete();

            return ApiResponseFunction::successResponse('User signed out successfully.', [], 200);
        } catch (Exception $e) {
            return ApiResponseFunction::errorResponse($e);
        }
    }

    public function signOutFromAllDevices(Request $request)
    {
        try {
            // Delete all tokens of the authenticated user
            $request->user()->tokens()->delete();

            return ApiResponseFunction::successResponse('User signed out from all devices.', [], 200);
        } catch (Exception $e) {
            return ApiResponseFunction::errorResponse($e);
        }
    }

    public function updateAuthUserData(RequestAuthUserUpdate $request)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return ApiResponseFunction::errorResponse('User not found!', 404);
            }

            // Update user fields using mass assignment
            $user->fill([
                'name' => $request->name,
                'gender_code' => $request->gender_code,
                'mobile_no' => $request->mobile_no ?? $user->mobile_no,
                'telephone_no' => $request->telephone_no ?? $user->telephone_no,
            ]);

            // Handle avatar upload if provided
            if ($request->hasFile('avatar')) {
                $user->avatar = FileUploadFunction::uploadImageFile($request->file('avatar'), 'uploads/avatars');
            }

            $user->save();
            
            $user->makeHidden(['created_by', 'updated_by', 'email_verified_at', 'is_active']);
            $user = HelperFunction::dataProcessor($user);

            return ApiResponseFunction::successResponse('User data updated successfully.', $user, 200);
        } catch (Exception $e) {
            return ApiResponseFunction::errorResponse($e->getMessage(), 500);
        }
    }
}
