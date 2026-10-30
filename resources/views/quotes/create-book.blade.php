@extends('layouts.app')

@section('title', __('Add Book Quote'))

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 py-10">
    <div class="editorial-card p-6 sm:p-9 space-y-6">

        <div class="flex items-start justify-between gap-4 border-b border-editorial pb-5">
            <div class="space-y-1.5">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-[#B99A68]/20 text-editorial-primary border border-[#B99A68]/40">
                    {{ __('Book Quote') }}
                </span>
                <h1 class="font-editorial text-2xl font-bold text-editorial-heading">
                    {{ __('Add Quote from a Book') }}
                </h1>
                <p class="text-sm text-editorial-secondary">
                    {{ __('Document a memorable passage from a book with its cover image and attributed author.') }}
                </p>
            </div>

            <a
                href="{{ route('quotes.create-quick') }}"
                class="px-3 py-1.5 rounded-xl text-xs font-semibold btn-editorial-secondary shrink-0"
            >
                {{ __('Switch to Quick Quote') }}
            </a>
        </div>

        <form method="POST" action="{{ route('quotes.store-book') }}" enctype="multipart/form-data" class="space-y-5" novalidate>
            @csrf

            {{-- 1. Book Title (Required) --}}
            <x-input
                name="book_title"
                type="text"
                :label="__('Book Title')"
                :placeholder="__('e.g., Al-Muqaddimah / Crime and Punishment')"
                required
                maxlength="150"
                autofocus
            />

            {{-- 2. Attributed Quote Author (Required — Separate from Platform Publisher) --}}
            <x-input
                name="attributed_author"
                type="text"
                :label="__('Attributed Quote Author')"
                :placeholder="__('Name of the author or person to whom the quote is attributed')"
                :hint="__('This is the original author of the quote. Your account (:name) will be shown separately as the platform publisher.', ['name' => auth()->user()->name])"
                required
                maxlength="120"
            />

            {{-- 3. Book Cover Image Upload (Required) --}}
            <div class="space-y-2">
                <label for="book_cover" class="block text-sm font-semibold text-editorial-heading">
                    {{ __('Book Cover Image') }}
                    <span class="text-[#B99A68]" aria-hidden="true">*</span>
                </label>

                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 p-4 rounded-xl bg-editorial-secondary/50 border border-dashed border-editorial-strong">
                    <div
                        id="book-cover-preview-wrapper"
                        class="w-16 h-22 rounded-lg bg-[#4A3328] text-[#FFFEFA] flex items-center justify-center shrink-0 overflow-hidden border border-editorial shadow-xs"
                    >
                        <img id="book-cover-preview" src="" alt="" class="hidden w-full h-full object-cover">
                        <svg id="book-cover-placeholder" class="w-6 h-6 text-[#B99A68]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                    </div>

                    <div class="space-y-1.5 flex-1 w-full">
                        <input
                            id="book_cover"
                            name="book_cover"
                            type="file"
                            accept="image/jpeg,image/png,image/webp"
                            required
                            class="block w-full text-xs text-editorial-secondary file:me-3 file:py-2 file:px-3.5 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-[#4A3328] file:text-[#FFFEFA] dark:file:bg-[#C9A66B] dark:file:text-[#211A16] hover:file:opacity-90 cursor-pointer"
                        >
                        <p class="text-xs text-editorial-secondary">
                            {{ __('Accepted formats: JPG, PNG, WEBP (maximum 4 MB).') }}
                        </p>
                    </div>
                </div>

                @error('book_cover')
                    <p class="text-xs font-medium text-red-600 dark:text-red-400" role="alert">{{ $message }}</p>
                @enderror
            </div>

            {{-- 4. Quote Text (Required) --}}
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
                    placeholder="{{ __('Enter the exact quote from the book...') }}"
                    class="w-full rounded-xl px-4 py-3 text-base font-editorial input-editorial transition-all leading-relaxed"
                >{{ old('quote_text') }}</textarea>

                @error('quote_text')
                    <p class="text-xs font-medium text-red-600 dark:text-red-400" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-editorial">
                <x-button variant="secondary" :href="route('home')">
                    {{ __('Cancel') }}
                </x-button>

                <x-button type="submit" variant="primary">
                    {{ __('Publish Book Quote') }}
                </x-button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var fileInput = document.getElementById('book_cover');
        var previewImg = document.getElementById('book-cover-preview');
        var placeholder = document.getElementById('book-cover-placeholder');

        if (!fileInput || !previewImg || !placeholder) return;

        fileInput.addEventListener('change', function (e) {
            var file = e.target.files && e.target.files[0];
            if (!file) {
                previewImg.src = '';
                previewImg.classList.add('hidden');
                placeholder.classList.remove('hidden');
                return;
            }

            var reader = new FileReader();
            reader.onload = function (ev) {
                previewImg.src = ev.target.result;
                previewImg.classList.remove('hidden');
                placeholder.classList.add('hidden');
            };
            reader.readAsDataURL(file);
        });
    });
</script>
@endpush
@endsection