<?php

namespace App\Http\Requests\Authentication;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
           'first_name' => 'required|string|min:3',
           'last_name' => 'required|string|min:3',
           'username' => 'required|string|unique:users',
           'email' => 'required|email:dns|unique:users',
           'password' => 'required|confirmed|string|min:6',
           'address' => 'nullable|string',
           'phone_number' => 'nullable|string|max:14'
        ];
        
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response([
            'validation_errors' => $validator->getMessageBag(),
        ] , 400));
    }
}
