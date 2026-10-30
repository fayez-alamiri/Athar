@extends('layouts.app')

@section('title', __('Edit Quote'))

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 py-10">
    <div class="editorial-card p-6 sm:p-9 space-y-6">

        <div class="flex items-start justify-between gap-4 border-b border-editorial pb-5">
            <div class="space-y-1.5">
                @if ($quote->isBookQuote())
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-[#B99A68]/20 text-editorial-primary border border-[#B99A68]/40">
                        {{ __('Book Quote') }}
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-editorial-secondary text-editorial-primary">
                        {{ __('Quick Quote') }}
                    </span>
                @endif

                <h1 class="font-editorial text-2xl font-bold text-editorial-heading">
                    {{ __('Edit Quote') }}
                </h1>
                <p class="text-sm text-editorial-secondary">
                    {{ $quote->isBookQuote()
                        ? __('Update the book details, attributed author, cover image, or quote text.')
                        : __('Update your quick quote text (between 3 and 155 characters).') }}
                </p>
            </div>

            <a
                href="{{ route('my-quotes.index') }}"
                class="px-3 py-1.5 rounded-xl text-xs font-semibold btn-editorial-secondary shrink-0"
            >
                {{ __('Back to My Quotes') }}
            </a>
        </div>

        <form
            method="POST"
            action="{{ route('my-quotes.update', $quote) }}"
            enctype="multipart/form-data"
            class="space-y-5"
            novalidate
        >
            @csrf
            @method('PUT')

            @if ($quote->isBookQuote())
                {{-- Book Title --}}
                <x-input
                    name="book_title"
                    type="text"
                    :label="__('Book Title')"
                    :value="$quote->book_title"
                    required
                    maxlength="150"
                />

                {{-- Attributed Author --}}
                <x-input
                    name="attributed_author"
                    type="text"
                    :label="__('Attributed Quote Author')"
                    :value="$quote->attributed_author"
                    required
                    maxlength="120"
                />

                {{-- Book Cover Image (Optional Replacement on Edit) --}}
                <div class="space-y-2">
                    <label for="book_cover" class="block text-sm font-semibold text-editorial-heading">
                        {{ __('Book Cover Image') }}
                    </label>

                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 p-4 rounded-xl bg-editorial-secondary/50 border border-dashed border-editorial-strong">
                        <div class="w-16 h-22 rounded-lg bg-[#4A3328] text-[#FFFEFA] flex items-center justify-center shrink-0 overflow-hidden border border-editorial shadow-xs">
                            @if ($quote->bookCoverUrl())
                                <img
                                    src="{{ $quote->bookCoverUrl() }}"
                                    alt="{{ $quote->book_title }}"
                                    class="w-full h-full object-cover"
                                >
                            @else
                                <svg class="w-6 h-6 text-[#B99A68]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                </svg>
                            @endif
                        </div>

                        <div class="space-y-1.5 flex-1 w-full">
                            <input
                                id="book_cover"
                                name="book_cover"
                                type="file"
                                accept="image/jpeg,image/png,image/webp,.jfif"
                                class="block w-full text-xs text-editorial-secondary file:me-3 file:py-2 file:px-3.5 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-[#4A3328] file:text-[#FFFEFA] dark:file:bg-[#C9A66B] dark:file:text-[#211A16] hover:file:opacity-90 cursor-pointer"
                            >
                            <p class="text-xs text-editorial-secondary">
                                {{ __('Leave empty to keep the current cover, or upload a new JPG, PNG, or WEBP image (max 4 MB).') }}
                            </p>
                        </div>
                    </div>

                    @error('book_cover')
                        <p class="text-xs font-medium text-red-600 dark:text-red-400" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Book Quote Text --}}
                <div class="space-y-1.5">
                    <label for="quote_text" class="block text-sm font-semibold text-editorial-heading">
                        {{ __('Quote Text') }}
                        <span class="text-[#B99A68]" aria-hidden="true">*</span>
                    </label>
                    <textarea
                        id="quote_text"
                        name="quote_text"
                        rows="5"
                        minlength="3"
                        maxlength="1000"
                        required
                        class="w-full rounded-xl px-4 py-3 text-base font-editorial input-editorial transition-all leading-relaxed"
                    >{{ old('quote_text', $quote->quote_text) }}</textarea>

                    @error('quote_text')
                        <p class="text-xs font-medium text-red-600 dark:text-red-400" role="alert">{{ $message }}</p>
                    @enderror
                </div>
            @else
                {{-- Quick Quote Text (3 to 155 characters) --}}
                <div class="space-y-2">
                    <div class="flex items-center justify-between gap-2">
                        <label for="quote_text" class="block text-sm font-semibold text-editorial-heading">
                            {{ __('Quote Text') }}
                            <span class="text-[#B99A68]" aria-hidden="true">*</span>
                        </label>
                        <span id="edit-quick-char-counter" class="text-xs font-medium text-editorial-secondary">
                            0 / 155
                        </span>
                    </div>

                    <textarea
                        id="quote_text"
                        name="quote_text"
                        rows="4"
                        minlength="3"
                        maxlength="155"
                        required
                        class="w-full rounded-xl px-4 py-3 text-base font-editorial input-editorial transition-all leading-relaxed"
                    >{{ old('quote_text', $quote->quote_text) }}</textarea>

                    @error('quote_text')
                        <p class="text-xs font-medium text-red-600 dark:text-red-400" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Optional Quick Quote Attributed Author --}}
                <x-input
                    name="attributed_author"
                    type="text"
                    :label="__('Attributed Quote Author') . ' (' . __('Optional') . ')'"
                    :value="$quote->attributed_author"
                    :placeholder="__('e.g., Al-Mutanabbi / Socrates (leave blank if your own thought)')"
                    maxlength="120"
                />
            @endif

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-editorial">
                <x-button variant="secondary" :href="route('my-quotes.index')">
                    {{ __('Cancel') }}
                </x-button>

                <x-button type="submit" variant="primary">
                    {{ __('Save Changes') }}
                </x-button>
            </div>
        </form>
    </div>
</div>

@if ($quote->isQuickQuote())
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var textarea = document.getElementById('quote_text');
            var counter = document.getElementById('edit-quick-char-counter');
            if (!textarea || !counter) return;

            function updateCount() {
                var length = Array.from(textarea.value.trim()).length;
                counter.textContent = length + ' / 155';
                if (length > 155 || (length > 0 && length < 3)) {
                    counter.classList.add('text-red-600', 'dark:text-red-400');
                } else {
                    counter.classList.remove('text-red-600', 'dark:text-red-400');
                }
            }

            textarea.addEventListener('input', updateCount);
            updateCount();
        });
    </script>
    @endpush
@endif
@endsection