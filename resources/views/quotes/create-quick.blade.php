@extends('layouts.app')

@section('title', __('Add Quick Quote'))

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 py-10">
    <div class="editorial-card p-6 sm:p-9 space-y-6">

        <div class="flex items-start justify-between gap-4 border-b border-editorial pb-5">
            <div class="space-y-1.5">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-editorial-secondary text-editorial-primary">
                    {{ __('Quick Quote') }}
                </span>
                <h1 class="font-editorial text-2xl font-bold text-editorial-heading">
                    {{ __('Add Quick Quote') }}
                </h1>
                <p class="text-sm text-editorial-secondary">
                    {{ __('Share a concise literary thought or reflection (between 3 and 155 characters).') }}
                </p>
            </div>

            <a
                href="{{ route('quotes.create-book') }}"
                class="px-3 py-1.5 rounded-xl text-xs font-semibold btn-editorial-secondary shrink-0"
            >
                {{ __('Switch to Book Quote') }}
            </a>
        </div>

        <form method="POST" action="{{ route('quotes.store-quick') }}" class="space-y-5" novalidate>
            @csrf

            <div class="space-y-2">
                <div class="flex items-center justify-between gap-2">
                    <label for="quote_text" class="block text-sm font-semibold text-editorial-heading">
                        {{ __('Quote Text') }}
                        <span class="text-[#B99A68]" aria-hidden="true">*</span>
                    </label>
                    <span id="quick-char-counter" class="text-xs font-medium text-editorial-secondary">
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
                    autofocus
                    placeholder="{{ __('Write your quote here (3 to 155 characters)...') }}"
                    class="w-full rounded-xl px-4 py-3 text-base font-editorial input-editorial transition-all leading-relaxed"
                >{{ old('quote_text') }}</textarea>

                @error('quote_text')
                    <p class="text-xs font-medium text-red-600 dark:text-red-400" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <x-input
                name="attributed_author"
                type="text"
                :label="__('Attributed Quote Author') . ' (' . __('Optional') . ')'"
                :placeholder="__('e.g., Al-Mutanabbi / Socrates (leave blank if your own thought)')"
                :hint="__('Your account name (:name) will be displayed as the publisher of this quote.', ['name' => auth()->user()->name])"
                maxlength="120"
            />

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-editorial">
                <x-button variant="secondary" :href="route('home')">
                    {{ __('Cancel') }}
                </x-button>

                <x-button type="submit" variant="primary">
                    {{ __('Publish Quick Quote') }}
                </x-button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var textarea = document.getElementById('quote_text');
        var counter = document.getElementById('quick-char-counter');
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
@endsection