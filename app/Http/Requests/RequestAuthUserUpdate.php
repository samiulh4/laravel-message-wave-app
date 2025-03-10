<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class RequestAuthUserUpdate extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'gender_code' => 'required|string|in:male,female,other',
            'mobile_no' => 'nullable|string|max:20',
            'telephone_no' => 'nullable|string|max:20',
            'avatar' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'username' => [
                'required',
                'string',
                'max:255',
                Rule::unique('users')->ignore(Auth::id()),  // Ignore the current user's username during validation
            ],
        ];
    }

    /**
     * Customize error messages.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The name field is required.',
            'name.max' => 'The name field is maximum 255 characters.',
            'avatar.image' => 'The avatar must be an image.',
            'avatar.mimes' => 'Allowed image formats: jpg, jpeg, png.',
            'avatar.max' => 'Avatar size must not exceed 2MB.',
            'gender_code.required' => 'The gender field is required.',
            'gender_code.in' => 'The gender must be in male, female, other.',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success'   => false,
            'message'   => 'Validation errors',
            'data'      => $validator->errors()
        ], 422));
    }
}
