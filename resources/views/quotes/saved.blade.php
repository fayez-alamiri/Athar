@extends('layouts.app')

@section('title', __('Saved Quotes'))

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10 space-y-8">

    {{-- Saved Quotes Header --}}
    <section class="editorial-card p-6 sm:p-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="space-y-2">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-editorial-secondary text-editorial-primary border border-editorial">
                    <svg class="w-3.5 h-3.5 text-[#B99A68]" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                    </svg>
                    <span>{{ __('Private Collection') }}</span>
                </div>

                <h1 class="font-editorial text-2xl sm:text-3xl font-bold text-editorial-heading">
                    {{ __('Saved Quotes') }}
                </h1>

                <p class="text-sm text-editorial-secondary leading-relaxed">
                    {{ __('Your personal reading sanctuary of saved user quotes and book passages.') }}
                </p>
            </div>

            <div class="flex items-center gap-3 shrink-0">
                <div class="px-4 py-2.5 rounded-xl bg-editorial-secondary/70 border border-editorial text-center">
                    <span class="block text-xs text-editorial-secondary">{{ __('Saved Quotes') }}</span>
                    <strong class="font-editorial text-lg font-bold text-editorial-heading" data-saved-total-count>
                        {{ $counts['all'] ?? 0 }}
                    </strong>
                </div>

                <x-button variant="secondary" :href="route('home')">
                    {{ __('Explore Feed') }}
                </x-button>
            </div>
        </div>
    </section>

    {{-- Search & Type Filter Bar for Saved Quotes --}}
    <section class="editorial-card p-4 sm:p-5 space-y-4">
        <form method="GET" action="{{ route('saved.index') }}" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
            @if ($activeType !== 'all')
                <input type="hidden" name="type" value="{{ $activeType }}">
            @endif

            <div class="relative flex-1">
                <label for="saved-search" class="sr-only">{{ __('Search in your saved quotes...') }}</label>
                <input
                    id="saved-search"
                    type="search"
                    name="q"
                    value="{{ $search }}"
                    placeholder="{{ __('Search in your saved quotes...') }}"
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
                        href="{{ route('saved.index', array_filter(['type' => $activeType !== 'all' ? $activeType : null])) }}"
                        class="px-3 py-2.5 rounded-xl text-xs font-semibold btn-editorial-secondary"
                    >
                        {{ __('Clear') }}
                    </a>
                @endif
            </div>
        </form>

        {{-- Filter Tabs: All | User Quotes | Book Quotes --}}
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
                        href="{{ route('saved.index', $tabParams) }}"
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

    {{-- Saved Quotes Grid or Empty State --}}
    @if ($quotes->isEmpty())
        <x-empty-state
            :title="$search !== '' || $activeType !== 'all' ? __('No matching saved quotes') : __('Your Saved Quotes collection is empty')"
            :description="$search !== '' || $activeType !== 'all' ? __('No saved quotes matched your current filter. Try clearing the search or switching tabs.') : __('When you save quick quotes or book quotes while browsing, they will appear here for easy reading and copying.')"
        >
            @if ($search !== '' || $activeType !== 'all')
                <x-button variant="secondary" :href="route('saved.index')">
                    {{ __('Reset Filters') }}
                </x-button>
            @endif

            <x-button variant="primary" :href="route('home')">
                {{ __('Explore Feed') }}
            </x-button>
        </x-empty-state>
    @else
        <section
            aria-label="{{ __('Saved Quotes') }}"
            data-saved-page-grid="true"
            class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
        >
            @foreach ($quotes as $quote)
                <x-quote-card :quote="$quote" />
            @endforeach
        </section>

        <div>
            {{ $quotes->links('vendor.pagination.editorial') }}
        </div>
    @endif

</div>
@endsection