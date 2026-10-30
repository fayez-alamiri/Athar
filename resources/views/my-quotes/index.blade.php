@extends('layouts.app')

@section('title', __('My Quotes'))

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10 space-y-8">

    {{-- My Quotes Header & Bulk Delete Control --}}
    <section class="editorial-card p-6 sm:p-8">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div class="space-y-2">
                <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-editorial-secondary text-editorial-primary border border-editorial">
                    <span class="w-2 h-2 rounded-full bg-[#B99A68]"></span>
                    <span>{{ __('Personal Publications') }}</span>
                </span>

                <h1 class="font-editorial text-2xl sm:text-3xl font-bold text-editorial-heading">
                    {{ __('My Quotes') }}
                </h1>

                <p class="text-sm text-editorial-secondary leading-relaxed">
                    {{ __('Manage all quick quotes and book quotes published by your account (:count total).', ['count' => $totalOwnedQuotes]) }}
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2.5 shrink-0">
                <x-button variant="primary" size="sm" :href="route('quotes.create-book')">
                    {{ __('Add Book Quote') }}
                </x-button>

                <x-button variant="secondary" size="sm" :href="route('quotes.create-quick')">
                    {{ __('Add Quick Quote') }}
                </x-button>

                @if ($totalOwnedQuotes > 0)
                    <button
                        type="button"
                        data-modal-open="delete-all-my-posts-modal"
                        class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-red-700/10 hover:bg-red-700 text-red-700 dark:text-red-300 hover:text-white border border-red-600/40 transition-colors cursor-pointer"
                    >
                        {{ __('Delete All My Posts') }}
                    </button>
                @endif
            </div>
        </div>
    </section>

    {{-- Search & Type Filter Tabs (All | User Quotes | Book Quotes) --}}
    <section class="editorial-card p-4 sm:p-5 space-y-4">
        <form method="GET" action="{{ route('my-quotes.index') }}" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
            @if ($activeType !== 'all')
                <input type="hidden" name="type" value="{{ $activeType }}">
            @endif

            <div class="relative flex-1">
                <label for="my-quotes-search" class="sr-only">{{ __('Search in your published quotes...') }}</label>
                <input
                    id="my-quotes-search"
                    type="search"
                    name="q"
                    value="{{ $search }}"
                    placeholder="{{ __('Search in your published quotes...') }}"
                    class="w-full rounded-xl ps-10 pe-4 py-2.5 text-sm input-editorial transition-all"
                >
                <svg class="w-4 h-4 text-editorial-secondary absolute start-3.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35m1.85-5.15a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>

            <div class="flex items-center gap-2">
                <x-button type="submit" variant="primary" size="md">
                    {{ __('Search') }}
                </x-button>

                @if ($search !== '')
                    <a
                        href="{{ route('my-quotes.index', array_filter(['type' => $activeType !== 'all' ? $activeType : null])) }}"
                        class="px-3 py-2.5 rounded-xl text-xs font-semibold btn-editorial-secondary"
                    >
                        {{ __('Clear') }}
                    </a>
                @endif
            </div>
        </form>

        <div class="flex flex-wrap items-center justify-between gap-3 pt-2 border-t border-editorial">
            <div class="inline-flex flex-wrap items-center gap-1.5" role="tablist" aria-label="{{ __('Filter quotes by type') }}">
                @php
                    $tabs = [
                        'all' => ['label' => __('All Quotes'), 'count' => $counts['all'] ?? 0],
                        'quick' => ['label' => __('User Quotes'), 'count' => $counts['quick'] ?? 0],
                        'book' => ['label' => __('Book Quotes'), 'count' => $counts['book'] ?? 0],
                    ];
                @endphp

                @foreach ($tabs as $tabKey => $tabInfo)
                    @php
                        $isActive = $activeType === $tabKey;
                        $tabParams = array_filter([
                            'type' => $tabKey !== 'all' ? $tabKey : null,
                            'q' => $search !== '' ? $search : null,
                        ]);
                    @endphp
                    <a
                        href="{{ route('my-quotes.index', $tabParams) }}"
                        role="tab"
                        aria-selected="{{ $isActive ? 'true' : 'false' }}"
                        class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs sm:text-sm font-semibold transition-all {{ $isActive ? 'bg-[#4A3328] text-[#FFFEFA] dark:bg-[#C9A66B] dark:text-[#211A16] shadow-xs' : 'bg-editorial-secondary/60 text-editorial-secondary hover:text-editorial-heading' }}"
                    >
                        <span>{{ $tabInfo['label'] }}</span>
                        <span class="px-1.5 py-0.5 rounded-md text-[11px] {{ $isActive ? 'bg-black/20 text-current' : 'bg-editorial-card text-editorial-secondary' }}">
                            {{ $tabInfo['count'] }}
                        </span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Quotes Grid or Empty State --}}
    @if ($quotes->isEmpty())
        <x-empty-state
            :title="$search !== '' || $activeType !== 'all' ? __('No matching quotes in your posts') : __('You have not published any quotes yet')"
            :description="$search !== '' || $activeType !== 'all' ? __('Try clearing the search query or switching tabs.') : __('Start sharing your favorite book passages or quick reflections with the community.')"
        >
            @if ($search !== '' || $activeType !== 'all')
                <x-button variant="secondary" :href="route('my-quotes.index')">
                    {{ __('Reset Filters') }}
                </x-button>
            @endif

            <x-button variant="primary" :href="route('quotes.create-quick')">
                {{ __('Add Quick Quote') }}
            </x-button>

            <x-button variant="secondary" :href="route('quotes.create-book')">
                {{ __('Add Book Quote') }}
            </x-button>
        </x-empty-state>
    @else
        <section aria-label="{{ __('My Quotes') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($quotes as $quote)
                <x-quote-card :quote="$quote" :showManagement="true" />
            @endforeach
        </section>

        <div>
            {{ $quotes->links('vendor.pagination.editorial') }}
        </div>
    @endif

</div>

{{-- Bulk Delete Confirmation Modal --}}
@if ($totalOwnedQuotes > 0)
    <x-modal id="delete-all-my-posts-modal" :title="__('Delete All My Posts')">
        <div class="space-y-4">
            <p class="text-sm text-editorial-secondary leading-relaxed">
                {{ __('Warning: This action will permanently delete all (:count) of your published quick quotes and book quotes. Dependent likes and bookmarks on those posts will also be removed. This cannot be undone.', ['count' => $totalOwnedQuotes]) }}
            </p>

            <div class="flex items-center justify-end gap-3 pt-2">
                <button
                    type="button"
                    data-modal-close="delete-all-my-posts-modal"
                    class="px-4 py-2 rounded-xl text-xs font-semibold btn-editorial-secondary cursor-pointer"
                >
                    {{ __('Cancel') }}
                </button>

                <form method="POST" action="{{ route('my-quotes.destroy-all') }}">
                    @csrf
                    @method('DELETE')
                    <x-button type="submit" variant="danger" size="sm">
                        {{ __('Yes, Delete All (:count)', ['count' => $totalOwnedQuotes]) }}
                    </x-button>
                </form>
            </div>
        </div>
    </x-modal>
@endif
@endsection