@extends('layouts.app')

@section('title', __('Zen Mode'))

@section('content')
<div class="min-h-[calc(100vh-2rem)] w-full flex flex-col items-center justify-center px-4 py-8 sm:py-12" data-zen-mode>
    <div class="w-full max-w-4xl flex items-center justify-between gap-4 mb-8">
        <a href="{{ route('home') }}" class="text-sm font-semibold text-editorial-secondary hover:text-editorial-heading">{{ __('ATHAR') }}</a>
        <a href="{{ route('home') }}" data-zen-exit class="px-4 py-2 rounded-xl btn-editorial-secondary text-sm font-semibold">{{ __('Exit Zen Mode') }} <span aria-hidden="true">×</span></a>
    </div>

    @if ($quotes->isEmpty())
        <div class="max-w-xl w-full text-center editorial-card p-8 sm:p-12 space-y-5">
            <p class="text-xs font-bold tracking-[0.2em] uppercase text-editorial-secondary">{{ __('A quieter place to read') }}</p>
            <h1 class="font-editorial text-3xl font-bold text-editorial-heading">{{ __('No quotations yet') }}</h1>
            <p class="text-sm text-editorial-secondary">{{ __('The reading room will be ready as soon as the first quote is published.') }}</p>
            <a href="{{ route('home') }}" class="inline-flex px-5 py-2.5 rounded-xl btn-editorial-primary text-sm font-semibold">{{ __('Return to the homepage') }}</a>
        </div>
    @else
        @php($quote = $quotes->first())
        <article class="w-full max-w-4xl flex-1 flex flex-col justify-center items-center text-center py-8 sm:py-16" aria-live="polite">
            <span class="text-xs font-bold tracking-[0.22em] uppercase text-[#B99A68] mb-8">{{ __('Zen Reading') }}</span>
            <blockquote dir="{{ preg_match('/\p{Arabic}/u', $quote->quote_text) ? 'rtl' : 'ltr' }}" class="font-editorial text-2xl sm:text-4xl lg:text-5xl leading-[1.8] text-editorial-heading whitespace-pre-line max-w-3xl">{{ $quote->quote_text }}</blockquote>
            @if ($quote->attributed_author)
                <p class="mt-8 text-base sm:text-lg text-editorial-secondary">— {{ $quote->attributed_author }}</p>
            @endif
            @if ($quote->book_title)
                <p class="mt-2 text-xs text-editorial-secondary">{{ __('From book') }}: {{ $quote->book_title }}</p>
            @endif
            <p class="mt-6 text-xs text-editorial-secondary">{{ __('Shared by') }} {{ $quote->user?->name ?? __('Reader') }}</p>
            <button type="button" data-copy-quote="{{ e($quote->quote_text) }}" data-copy-author="{{ e($quote->attributed_author ?? '') }}" data-copy-success="{{ __('Copied to clipboard') }}" data-copy-error="{{ __('Unable to copy quote') }}" class="mt-9 px-5 py-2.5 rounded-xl btn-editorial-secondary text-sm font-semibold">{{ __('Copy quote') }}</button>
        </article>

        <nav class="w-full max-w-4xl flex items-center justify-between gap-4 mt-8" aria-label="{{ __('Zen reading navigation') }}">
            @if ($quotes->onFirstPage())
                <span class="px-4 py-2 rounded-xl opacity-40 btn-editorial-secondary text-sm">{{ __('Previous') }}</span>
            @else
                <a rel="prev" data-zen-prev href="{{ $quotes->previousPageUrl() }}" class="px-4 py-2 rounded-xl btn-editorial-secondary text-sm font-semibold">← {{ __('Previous') }}</a>
            @endif
            <span class="text-xs text-editorial-secondary">{{ $quotes->currentPage() }} / {{ $quotes->lastPage() }}</span>
            @if ($quotes->hasMorePages())
                <a rel="next" data-zen-next href="{{ $quotes->nextPageUrl() }}" class="px-4 py-2 rounded-xl btn-editorial-secondary text-sm font-semibold">{{ __('Next') }} →</a>
            @else
                <span class="px-4 py-2 rounded-xl opacity-40 btn-editorial-secondary text-sm">{{ __('Next') }}</span>
            @endif
        </nav>
    @endif
</div>
@endsection

@push('scripts')
<script>
(() => {
    const root = document.querySelector('[data-zen-mode]');
    if (!root) return;
    document.addEventListener('keydown', (event) => {
        if (event.altKey || event.ctrlKey || event.metaKey) return;
        const target = event.target;
        if (target && (target.matches('input, textarea, select, [contenteditable="true"]'))) return;
        if (event.key === 'Escape') {
            window.location.href = @json(route('home'));
        } else if (event.key === 'ArrowLeft' || event.key === 'ArrowRight') {
            const previous = root.querySelector('[data-zen-prev]');
            const next = root.querySelector('[data-zen-next]');
            const rtl = document.documentElement.dir === 'rtl';
            const goPrevious = (event.key === 'ArrowLeft') !== rtl;
            const link = goPrevious ? previous : next;
            if (link) window.location.href = link.href;
        }
    });
})();
</script>
@endpush
