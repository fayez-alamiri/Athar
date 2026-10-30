@extends('layouts.app')

@section('title', $author->name)

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10 space-y-8">
    <section class="editorial-card p-7 sm:p-10 flex flex-col sm:flex-row sm:items-center gap-5">
        <span class="w-16 h-16 shrink-0 rounded-2xl theme-brand-pill flex items-center justify-center font-editorial text-3xl font-bold" aria-hidden="true">{{ mb_substr($author->name, 0, 1) }}</span>
        <div class="min-w-0 flex-1 space-y-2">
            <a href="{{ route('authors.index') }}" class="text-xs font-semibold text-editorial-secondary hover:text-editorial-heading">← {{ __('All authors') }}</a>
            <h1 class="font-editorial text-3xl sm:text-4xl font-bold text-editorial-heading">{{ $author->name }}</h1>
            @if ($author->bio)
                <p class="text-sm text-editorial-secondary leading-relaxed">{{ $author->bio }}</p>
            @endif
            <p class="text-xs text-editorial-secondary">{{ trans_choice(':count quotation|:count quotations', $quotes->total(), ['count' => $quotes->total()]) }}</p>
        </div>
    </section>

    @if ($quotes->isEmpty())
        <x-empty-state :title="__('No quotations by this author yet')" :description="__('When a reader shares a quotation attributed to this author, it will appear here.')">
            <x-button variant="secondary" :href="route('authors.index')">{{ __('All authors') }}</x-button>
        </x-empty-state>
    @else
        <section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" aria-label="{{ __('Quotes by :author', ['author' => $author->name]) }}">
            @foreach ($quotes as $quote)
                <x-quote-card :quote="$quote" />
            @endforeach
        </section>
        <div>{{ $quotes->links('vendor.pagination.editorial') }}</div>
    @endif
</div>
@endsection
