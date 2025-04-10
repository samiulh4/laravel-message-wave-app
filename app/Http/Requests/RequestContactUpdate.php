<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Functions\EncryptionFunction;

class RequestContactUpdate extends FormRequest
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
        $contactId = $this->route('id'); // Get the contact ID from the route parameter
        $contactId = EncryptionFunction::decodeId($contactId);

        return [
            'contact_name' => 'required|string|max:255',
            'contact_email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('message_contacts')->ignore($contactId)
            ],
            'contact_mobile' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('message_contacts')->ignore($contactId)
            ],
            'contact_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048'
        ];
    }

    /**
     * Get the validation messages that apply to the request.
     */
    public function messages(): array
    {
        return [
            'user_id.required' => 'The user ID is required.',
            'user_id.integer' => 'The user ID must be an integer.',
            'contact_name.required' => 'The contact name is required.',
            'contact_name.string' => 'The contact name must be a string.',
            'contact_name.max' => 'The contact name may not be greater than 255 characters.',
            'contact_email.email' => 'The contact email must be a valid email address.',
            'contact_email.max' => 'The contact email may not be greater than 255 characters.',
            'contact_mobile.string' => 'The contact mobile must be a string.',
            'contact_mobile.max' => 'The contact mobile may not be greater than 20 characters.'
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
