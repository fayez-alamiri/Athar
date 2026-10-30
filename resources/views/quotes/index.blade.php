@extends('layouts.app')

@section('title', __('Home'))

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10 space-y-8">

    {{-- Editorial Hero Header --}}
    <section class="editorial-card p-7 sm:p-10 relative overflow-hidden">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div class="max-w-2xl space-y-3">
                <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-editorial-secondary text-editorial-primary border border-editorial">
                    <span class="w-2 h-2 rounded-full bg-[#B99A68]"></span>
                    <span>{{ __('Editorial Reading Sanctuary') }}</span>
                </span>

                <h1
                    class="font-editorial text-2xl sm:text-4xl font-bold text-editorial-heading leading-tight min-h-[1.25em]"
                    data-typewriter="{{ $brandName ?? config('brand.name.' . app()->getLocale(), 'ATHAR') }}"
                >
                    {{ $brandName ?? config('brand.name.' . app()->getLocale(), 'ATHAR') }}
                </h1>

                <p class="text-sm sm:text-base text-editorial-secondary leading-relaxed">
                    {{ __('Curate, reflect on, and share timeless passages from books and fellow readers.') }}
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3 shrink-0">
                @auth
                    <x-button variant="primary" :href="route('quotes.create-book')">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                        <span>{{ __('Add Book Quote') }}</span>
                    </x-button>

                    <x-button variant="secondary" :href="route('quotes.create-quick')">
                        <svg class="w-4 h-4 text-[#B99A68]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>{{ __('Add Quick Quote') }}</span>
                    </x-button>
                @else
                    <x-button variant="primary" data-require-auth="publish-book">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                        <span>{{ __('Add Book Quote') }}</span>
                    </x-button>

                    <x-button variant="secondary" data-require-auth="publish-quick">
                        <svg class="w-4 h-4 text-[#B99A68]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>{{ __('Add Quick Quote') }}</span>
                    </x-button>
                @endauth
            </div>
        </div>
    </section>

    @if ($quoteOfTheDay)
        <section class="space-y-4" aria-labelledby="daily-quote-heading">
            <div class="flex items-end justify-between gap-3">
                <div>
                    <p class="text-xs font-bold tracking-[0.18em] uppercase text-[#B99A68]">{{ __('A little inspiration for today') }}</p>
                    <h2 id="daily-quote-heading" class="font-editorial text-xl sm:text-2xl font-bold text-editorial-heading mt-1">{{ __('Quote of the Day') }}</h2>
                </div>
                <a href="{{ route('quotes.daily') }}" class="text-xs font-semibold text-editorial-secondary hover:text-editorial-heading">{{ __('Read daily quote') }} →</a>
            </div>
            <div class="max-w-3xl mx-auto">
                <x-quote-card :quote="$quoteOfTheDay" />
            </div>
        </section>
    @endif

    {{-- Search Bar, Content Type Filter Tabs & Sorting --}}
    <section class="editorial-card p-4 sm:p-5 space-y-4">
        <form method="GET" action="{{ route('home') }}" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
            @if ($activeType !== 'all')
                <input type="hidden" name="type" value="{{ $activeType }}">
            @endif

            <div class="relative flex-1">
                <label for="feed-search" class="sr-only">{{ __('Search quotes, books, or authors...') }}</label>
                <input
                    id="feed-search"
                    type="search"
                    name="q"
                    value="{{ $search }}"
                    placeholder="{{ __('Search quotes, books, or authors...') }}"
                    class="w-full rounded-xl ps-10 pe-4 py-2.5 text-sm input-editorial transition-all"
                >
                <svg class="w-4 h-4 text-editorial-secondary absolute start-3.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35m1.85-5.15a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>

            <div class="flex items-center gap-2">
                <label for="feed-sort" class="sr-only">{{ __('Sort by') }}</label>
                <select
                    id="feed-sort"
                    name="sort"
                    onchange="this.form.submit()"
                    class="rounded-xl px-3.5 py-2.5 text-xs sm:text-sm font-medium input-editorial cursor-pointer"
                >
                    <option value="latest" @selected($sort === 'latest')>{{ __('Newest First') }}</option>
                    <option value="popular" @selected($sort === 'popular')>{{ __('Most Liked') }}</option>
                </select>

                <x-button type="submit" variant="primary" size="md">
                    {{ __('Search') }}
                </x-button>

                @if ($search !== '')
                    <a
                        href="{{ route('home', array_filter(['type' => $activeType !== 'all' ? $activeType : null, 'sort' => $sort !== 'latest' ? $sort : null])) }}"
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
                            'sort' => $sort !== 'latest' ? $sort : null,
                        ]);
                    @endphp
                    <a
                        href="{{ route('home', $tabParams) }}"
                        role="tab"
                        aria-selected="{{ $isActive ? 'true' : 'false' }}"
                        class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs sm:text-sm font-semibold transition-all {{ $isActive ? 'theme-brand-pill shadow-xs' : 'bg-editorial-secondary/60 text-editorial-secondary hover:text-editorial-heading' }}"
                    >
                        <span>{{ $tabInfo['label'] }}</span>
                        <span class="px-1.5 py-0.5 rounded-md text-[11px] {{ $isActive ? 'bg-black/20 text-current' : 'bg-editorial-card text-editorial-secondary' }}">
                            {{ $tabInfo['count'] }}
                        </span>
                    </a>
                @endforeach
            </div>

            @if ($search !== '')
                <p class="text-xs text-editorial-secondary">
                    {{ __('Search results for') }}: <strong class="text-editorial-heading">“{{ $search }}”</strong>
                </p>
            @endif
        </div>
    </section>

    {{-- Quote Cards Grid or Empty State --}}
    @if ($quotes->isEmpty())
        <x-empty-state
            :title="$search !== '' ? __('No matching quotes found') : __('No quotes found')"
            :description="$search !== '' ? __('No quotes matched your search query. Try different keywords or reset the filter.') : __('Try adjusting your search or filter, or publish the first quote in this space.')"
        >
            @if ($search !== '' || $activeType !== 'all')
                <x-button variant="secondary" :href="route('home')">
                    {{ __('Reset Filters') }}
                </x-button>
            @endif

            @auth
                <x-button variant="primary" :href="route('quotes.create-quick')">
                    {{ __('Add Quick Quote') }}
                </x-button>
            @else
                <x-button variant="primary" data-require-auth="empty-publish">
                    {{ __('Add Quick Quote') }}
                </x-button>
            @endauth
        </x-empty-state>
    @else
        <section aria-label="{{ __('Quotes Feed') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($quotes as $quote)
                <x-quote-card :quote="$quote" />
            @endforeach
        </section>

        {{-- Pagination Links --}}
        <div>
            {{ $quotes->links('vendor.pagination.editorial') }}
        </div>
    @endif

</div>
@endsection