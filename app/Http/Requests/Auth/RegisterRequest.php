<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * SRS Validation Rules:
     * - Name: 2 to 50 characters
     * - Email: Valid, unique email
     * - Password: At least 8 characters, confirmed
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:50'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => __('Please enter your name.'),
            'name.min' => __('Name must be between 2 and 50 characters.'),
            'name.max' => __('Name must be between 2 and 50 characters.'),
            'email.required' => __('Please enter your email address.'),
            'email.email' => __('Please enter a valid email address.'),
            'email.unique' => __('This email address is already registered.'),
            'password.required' => __('Please enter a password.'),
            'password.min' => __('Password must be at least 8 characters long.'),
            'password.confirmed' => __('Password confirmation does not match.'),
        ];
    }
}