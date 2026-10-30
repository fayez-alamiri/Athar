<?php

namespace App\Http\Requests;

use App\Models\Quote;
use Illuminate\Foundation\Http\FormRequest;

class UpdateQuoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Quote|null $quote */
        $quote = $this->route('quote');

        return $quote && $this->user() && $this->user()->can('update', $quote);
    }

    /**
     * Apply conditional validation rules matching the quote's type (quick vs. book).
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var Quote $quote */
        $quote = $this->route('quote');

        if ($quote && $quote->isBookQuote()) {
            return [
                'book_title' => ['required', 'string', 'min:1', 'max:150'],
                'book_cover' => ['nullable', 'file', 'image', 'mimes:jpeg,jpg,jfif,png,webp', 'max:4096'],
                'quote_text' => ['required', 'string', 'min:3', 'max:1000'],
                'attributed_author' => ['required', 'string', 'min:2', 'max:120'],
            ];
        }

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

        if ($this->has('book_title')) {
            $merged['book_title'] = trim((string) $this->input('book_title'));
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
            'quote_text.min' => __('Quote text must be at least 3 characters.'),
            'quote_text.max' => __('Quick quote text must be between 3 and 155 characters.'),
            'book_title.required' => __('Please enter the book title.'),
            'book_cover.image' => __('The book cover must be a valid image file (JPG, PNG, or WEBP).'),
            'book_cover.mimes' => __('The book cover must be a JPG, PNG, or WEBP image.'),
            'book_cover.max' => __('The book cover image must not exceed 4 MB.'),
            'attributed_author.required' => __('Please enter the name of the person to whom the quote is attributed.'),
            'attributed_author.min' => __('Attributed author name must be at least 2 characters.'),
            'attributed_author.max' => __('Attributed author name must not exceed 120 characters.'),
        ];
    }
}