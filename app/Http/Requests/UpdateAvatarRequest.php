<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAvatarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Validate uploaded avatar by actual image content/MIME type and file size (max 2 MB).
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'avatar' => ['required', 'file', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'avatar.required' => __('Please select an image file to upload as your avatar.'),
            'avatar.uploaded' => __('The avatar image must not exceed 2 MB.'),
            'avatar.image' => __('The avatar must be a valid image file (JPG, PNG, or WEBP).'),
            'avatar.mimes' => __('The avatar must be a JPG, PNG, or WEBP image.'),
            'avatar.max' => __('The avatar image must not exceed 2 MB.'),
        ];
    }
}