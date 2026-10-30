<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookQuoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Four mandatory fields for a Book Quote (Section 8.2):
     * 1. book_title
     * 2. book_cover (uploaded image file validated by content/MIME and size)
     * 3. quote_text
     * 4. attributed_author
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'book_title' => ['required', 'string', 'min:1', 'max:150'],
            'book_cover' => ['required', 'file', 'image', 'mimes:jpeg,jpg,jfif,png,webp', 'max:4096'],
            'quote_text' => ['required', 'string', 'min:3', 'max:1000'],
            'attributed_author' => ['required', 'string', 'min:2', 'max:120'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'book_title' => $this->has('book_title') ? trim((string) $this->input('book_title')) : null,
            'quote_text' => $this->has('quote_text') ? trim((string) $this->input('quote_text')) : null,
            'attributed_author' => $this->has('attributed_author') ? trim((string) $this->input('attributed_author')) : null,
        ]);
    }

    public function messages(): array
    {
        return [
            'book_title.required' => __('Please enter the book title.'),
            'book_cover.required' => __('Please upload a book cover image.'),
            'book_cover.image' => __('The book cover must be a valid image file (JPG, PNG, or WEBP).'),
            'book_cover.mimes' => __('The book cover must be a JPG, PNG, or WEBP image.'),
            'book_cover.max' => __('The book cover image must not exceed 4 MB.'),
            'quote_text.required' => __('Please enter the quote text.'),
            'quote_text.min' => __('Quote text must be at least 3 characters.'),
            'attributed_author.required' => __('Please enter the name of the person to whom the quote is attributed.'),
            'attributed_author.min' => __('Attributed author name must be at least 2 characters.'),
        ];
    }
}