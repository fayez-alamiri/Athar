@props([
    'quote',
    'showManagement' => false,
])

@php
    $isBook = $quote->isBookQuote();
    $publisherName = $quote->user?->name ?? __('Reader');
    $hasAttributedAuthor = filled($quote->attributed_author);

    $copyAuthor = $isBook
        ? ($quote->attributed_author . ($quote->book_title ? ' (' . $quote->book_title . ')' : ''))
        : ($hasAttributedAuthor ? $quote->attributed_author : $publisherName);

    $likesCount = $quote->likes_count ?? $quote->likes()->count();
    $isLiked = auth()->check()
        ? (isset($quote->is_liked_by_auth) ? (bool) $quote->is_liked_by_auth : $quote->isLikedBy(auth()->user()))
        : false;
    $isSaved = auth()->check()
        ? (isset($quote->is_saved_by_auth) ? (bool) $quote->is_saved_by_auth : $quote->isSavedBy(auth()->user()))
        : false;

    $coverUrl = $isBook ? $quote->bookCoverUrl() : null;

    // Detect if quote text is predominantly Arabic or Latin for natural reading alignment & quotation marks
    $isArabicText = preg_match('/\p{Arabic}/u', (string) $quote->quote_text) === 1;
    $quoteTextDir = $isArabicText ? 'rtl' : 'ltr';
    $openQuoteMark = $isArabicText ? '«' : '“';
    $closeQuoteMark = $isArabicText ? '»' : '”';

    // Detect direction for quick-quote author attribution line
    $isArabicAuthor = $hasAttributedAuthor && preg_match('/\p{Arabic}/u', (string) $quote->attributed_author) === 1;
    $authorTextDir = $hasAttributedAuthor ? ($isArabicAuthor ? 'rtl' : $quoteTextDir) : $quoteTextDir;

    $deleteModalId = 'delete-quote-modal-' . $quote->id;
@endphp

<article
    id="quote-card-{{ $quote->id }}"
    class="editorial-card p-6 flex flex-col justify-between gap-5 h-full"
    data-quote-card="{{ $quote->id }}"
    data-quote-id="{{ $quote->id }}"
    data-quote-type="{{ $quote->type }}"
    data-quote-text="{{ e($quote->quote_text) }}"
    data-quote-publisher="{{ e($publisherName) }}"
    data-quote-author="{{ e($quote->attributed_author ?? '') }}"
    data-quote-book="{{ e($quote->book_title ?? '') }}"
    data-quote-cover="{{ $coverUrl ?? '' }}"
>
    {{-- Top Section: Type Badge, Date & Quote Body --}}
    <div class="space-y-4">
        <div class="flex items-center justify-between gap-2 flex-wrap">
            <div class="flex items-center gap-2">
                @if ($isBook)
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-[#B99A68]/20 text-editorial-primary border border-[#B99A68]/40">
                        <svg class="w-3.5 h-3.5 text-[#B99A68]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                        <span>{{ __('Book Quote') }}</span>
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-editorial-secondary text-editorial-primary border border-editorial">
                        <svg class="w-3.5 h-3.5 text-editorial-secondary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 8h10M7 12h6m-6 8l-4-4V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                        </svg>
                        <span>{{ __('Quick Quote') }}</span>
                    </span>
                @endif
            </div>

            <time
                datetime="{{ $quote->created_at?->toIso8601String() }}"
                class="text-xs text-editorial-secondary"
            >
                {{ $quote->created_at?->diffForHumans() }}
            </time>
        </div>

        {{-- Main Literary Quote Text + Immediate Author Attribution for Quick Quotes --}}
        <div class="space-y-2.5">
            <blockquote
                dir="{{ $quoteTextDir }}"
                class="font-editorial text-lg sm:text-xl leading-relaxed text-editorial-heading wrap-break-word whitespace-pre-line"
            >{{ $openQuoteMark }}{{ $quote->quote_text }}{{ $closeQuoteMark }}</blockquote>

            @if (! $isBook && $hasAttributedAuthor)
                <p
                    dir="{{ $authorTextDir }}"
                    data-quick-quote-attribution="{{ $quote->id }}"
                    class="text-sm sm:text-base font-medium text-editorial-secondary tracking-wide leading-snug"
                >
                    — {{ $quote->attributed_author }}
                </p>
            @endif
        </div>

        {{-- Book Metadata Block (Only for Book Quotes) --}}
        @if ($isBook)
            <div class="flex items-center gap-3.5 p-3 rounded-xl bg-editorial-secondary/60 border border-editorial">
                {{-- Book Cover Image with Graceful Fallback --}}
                <div class="w-12 h-16 rounded-lg overflow-hidden bg-[#4A3328] text-[#FFFEFA] flex items-center justify-center shrink-0 border border-editorial shadow-xs relative">
                    @if ($coverUrl)
                        <img
                            src="{{ $coverUrl }}"
                            alt="{{ __('Cover of :book', ['book' => $quote->book_title]) }}"
                            loading="lazy"
                            class="w-full h-full object-cover"
                            onerror="this.classList.add('hidden'); this.nextElementSibling.classList.remove('hidden');"
                        >
                        <div class="hidden w-full h-full flex flex-col items-center justify-center p-1 text-center bg-[#4A3328] text-[#FFFEFA]">
                            <svg class="w-5 h-5 text-[#B99A68]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                        </div>
                    @else
                        <div class="w-full h-full flex flex-col items-center justify-center p-1 text-center bg-[#4A3328] text-[#FFFEFA]">
                            <svg class="w-5 h-5 text-[#B99A68]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                        </div>
                    @endif
                </div>

                <div class="min-w-0 flex-1 space-y-1 text-xs">
                    <p class="font-bold text-editorial-heading truncate" title="{{ $quote->book_title }}">
                        <span class="text-editorial-secondary font-normal">{{ __('From book') }}:</span>
                        {{ $quote->book_title }}
                    </p>
                    <p class="text-editorial-secondary truncate" title="{{ $quote->attributed_author }}">
                        <span class="font-medium text-editorial-primary">{{ __('Attributed to') }}:</span>
                        {{ $quote->attributed_author }}
                    </p>
                </div>
            </div>
        @endif
    </div>

    {{-- Bottom Section: Platform Publisher & Action Controls --}}
    <div class="pt-4 border-t border-editorial space-y-3">
        <div class="flex items-center justify-between gap-3">
            <div class="flex items-center gap-2.5 min-w-0">
                <x-avatar :user="$quote->user" size="sm" />
                <div class="min-w-0">
                    <p class="text-[11px] text-editorial-secondary leading-none">{{ __('Published by') }}</p>
                    <p class="text-xs font-bold text-editorial-heading truncate mt-0.5">{{ $publisherName }}</p>
                </div>
            </div>

            {{-- Owner Edit & Delete Controls (Protected by QuotePolicy) --}}
            @can('update', $quote)
                <div class="flex items-center gap-1.5 shrink-0">
                    <a
                        href="{{ route('my-quotes.edit', $quote) }}"
                        class="px-2.5 py-1 rounded-lg text-xs font-semibold btn-editorial-secondary"
                    >
                        {{ __('Edit') }}
                    </a>

                    <button
                        type="button"
                        data-modal-open="{{ $deleteModalId }}"
                        class="px-2.5 py-1 rounded-lg text-xs font-semibold btn-editorial-secondary text-red-700 dark:text-red-400 hover:border-red-500 cursor-pointer"
                    >
                        {{ __('Delete') }}
                    </button>
                </div>
            @endcan
        </div>

        {{-- Core Quote Actions: Like, Save, Copy, Share --}}
        <div class="flex flex-wrap items-center justify-between gap-2 pt-1">
            <div class="flex items-center gap-1.5">
                {{-- Like Control --}}
                @auth
                    <form method="POST" action="{{ route('quotes.like.toggle', $quote, false) }}" data-like-form="{{ $quote->id }}">
                        @csrf
                        <button
                            type="submit"
                            data-like-button="{{ $quote->id }}"
                            aria-pressed="{{ $isLiked ? 'true' : 'false' }}"
                            aria-label="{{ __('Like') }}"
                            class="px-3 py-1.5 rounded-xl text-xs font-semibold inline-flex items-center gap-1.5 transition-all cursor-pointer {{ $isLiked ? 'theme-brand-pill' : 'btn-editorial-secondary' }}"
                        >
                            <svg data-like-icon="{{ $quote->id }}" class="w-3.5 h-3.5 pointer-events-none {{ $isLiked ? 'fill-current' : 'fill-none' }}" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                            </svg>
                            <span data-like-count="{{ $quote->id }}" class="pointer-events-none">{{ $likesCount }}</span>
                        </button>
                    </form>
                @else
                    <button
                        type="button"
                        data-require-auth="like"
                        data-modal-open="guest-auth-modal"
                        class="px-3 py-1.5 rounded-xl text-xs font-semibold inline-flex items-center gap-1.5 btn-editorial-secondary cursor-pointer"
                        aria-label="{{ __('Like') }}"
                    >
                        <svg class="w-3.5 h-3.5 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                        </svg>
                        <span class="pointer-events-none">{{ $likesCount }}</span>
                    </button>
                @endauth

                {{-- Save / Bookmark Control --}}
                @auth
                    <form method="POST" action="{{ route('quotes.save.toggle', $quote, false) }}" data-save-form="{{ $quote->id }}">
                        @csrf
                        <button
                            type="submit"
                            data-save-button="{{ $quote->id }}"
                            data-label-save="{{ __('Save') }}"
                            data-label-saved="{{ __('Saved') }}"
                            aria-pressed="{{ $isSaved ? 'true' : 'false' }}"
                            class="px-3 py-1.5 rounded-xl text-xs font-semibold inline-flex items-center gap-1.5 transition-all cursor-pointer {{ $isSaved ? 'bg-[#B99A68] text-[#2B1D17]' : 'btn-editorial-secondary' }}"
                        >
                            <svg data-save-icon="{{ $quote->id }}" class="w-3.5 h-3.5 pointer-events-none {{ $isSaved ? 'fill-current' : 'fill-none' }}" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                            </svg>
                            <span data-save-label="{{ $quote->id }}" class="pointer-events-none">{{ $isSaved ? __('Saved') : __('Save') }}</span>
                        </button>
                    </form>
                @else
                    <button
                        type="button"
                        data-require-auth="save"
                        data-modal-open="guest-auth-modal"
                        class="px-3 py-1.5 rounded-xl text-xs font-semibold inline-flex items-center gap-1.5 btn-editorial-secondary cursor-pointer"
                    >
                        <svg class="w-3.5 h-3.5 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                        </svg>
                        <span class="pointer-events-none">{{ __('Save') }}</span>
                    </button>
                @endauth
            </div>

            {{-- Independent Copy Action & PNG Share Generator Trigger (Available to Guests and Auth Users) --}}
            <div class="flex items-center gap-1.5">
                <button
                    type="button"
                    data-copy-quote="{{ e($quote->quote_text) }}"
                    data-copy-author="{{ e($copyAuthor) }}"
                    data-copy-success="{{ __('Copied to clipboard') }}"
                    data-copy-error="{{ __('Unable to copy quote') }}"
                    class="px-3 py-1.5 rounded-xl text-xs font-semibold inline-flex items-center gap-1.5 btn-editorial-secondary cursor-pointer"
                >
                    <svg class="w-3.5 h-3.5 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                    <span class="pointer-events-none">{{ __('Copy') }}</span>
                </button>

                <button
                    type="button"
                    data-share-quote="{{ $quote->id }}"
                    aria-label="{{ __('Share as Image') }}"
                    class="px-3 py-1.5 rounded-xl text-xs font-semibold inline-flex items-center gap-1.5 btn-editorial-secondary cursor-pointer"
                >
                    <svg class="w-3.5 h-3.5 text-[#B99A68] pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    <span class="pointer-events-none">{{ __('Share') }}</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Individual Quote Deletion Confirmation Modal (Rendered only for the quote owner) --}}
    @can('delete', $quote)
        <x-modal :id="$deleteModalId" :title="__('Delete Quote')">
            <div class="space-y-4">
                <p class="text-sm text-editorial-secondary leading-relaxed">
                    {{ __('Are you sure you want to permanently delete this quote? This action cannot be undone.') }}
                </p>

                <blockquote class="p-3.5 rounded-xl bg-editorial-secondary/60 border border-editorial font-editorial text-sm text-editorial-heading line-clamp-3">
                    {{ $openQuoteMark }}{{ $quote->quote_text }}{{ $closeQuoteMark }}
                </blockquote>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button
                        type="button"
                        data-modal-close="{{ $deleteModalId }}"
                        class="px-4 py-2 rounded-xl text-xs font-semibold btn-editorial-secondary cursor-pointer"
                    >
                        {{ __('Cancel') }}
                    </button>

                    <form method="POST" action="{{ route('my-quotes.destroy', $quote, false) }}">
                        @csrf
                        @method('DELETE')
                        <x-button type="submit" variant="danger" size="sm">
                            {{ __('Confirm Delete') }}
                        </x-button>
                    </form>
                </div>
            </div>
        </x-modal>
    @endcan
</article>