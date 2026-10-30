<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreQuickQuoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * SRS Validation Rule for Quick Quotes:
     * - Quote text: required, 3 to 155 characters.
     * - Attributed author: optional string up to 120 characters.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'quote_text' => ['required', 'string', 'min:3', 'max:155'],
            'attributed_author' => ['nullable', 'string', 'max:120'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $merged = [];

        if ($this->has('quote_text')) {
            $merged['quote_text'] = trim((string) $this->input('quote_text'));
        }

        if ($this->has('attributed_author')) {
            $author = trim((string) $this->input('attributed_author'));
            $merged['attributed_author'] = $author !== '' ? $author : null;
        }

        if (! empty($merged)) {
            $this->merge($merged);
        }
    }

    public function messages(): array
    {
        return [
            'quote_text.required' => __('Please enter the quote text.'),
            'quote_text.min' => __('Quick quote text must be between 3 and 155 characters.'),
            'quote_text.max' => __('Quick quote text must be between 3 and 155 characters.'),
            'attributed_author.max' => __('Attributed author name must not exceed 120 characters.'),
        ];
    }
}