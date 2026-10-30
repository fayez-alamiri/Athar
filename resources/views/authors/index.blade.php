@extends('layouts.app')

@section('title', __('Authors'))

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10 space-y-8">
    <section class="editorial-card relative overflow-hidden p-7 sm:p-10">
        <div class="absolute -end-12 -top-12 w-48 h-48 rounded-full bg-[#B99A68]/10 blur-3xl pointer-events-none"></div>
        <div class="relative max-w-3xl space-y-3">
            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-editorial-secondary text-editorial-primary border border-editorial">{{ __('The voices behind the words') }}</span>
            <h1 class="font-editorial text-3xl sm:text-4xl font-bold text-editorial-heading">{{ __('Authors') }}</h1>
            <p class="text-sm sm:text-base text-editorial-secondary leading-relaxed">{{ __('Discover writers and thinkers through the quotations shared by readers.') }}</p>
        </div>
    </section>

    @if ($authors->isEmpty())
        <x-empty-state :title="__('No authors yet')" :description="__('Authors will appear here as readers add attributions to their quotations.')">
            <x-button variant="primary" :href="route('home')">{{ __('Explore quotes') }}</x-button>
        </x-empty-state>
    @else
        <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5" aria-label="{{ __('Authors') }}">
            @foreach ($authors as $author)
                <a href="{{ route('authors.show', $author) }}" class="editorial-card group p-6 flex items-start gap-4 transition-transform hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-[#B99A68]">
                    <span class="w-12 h-12 shrink-0 rounded-2xl theme-brand-pill flex items-center justify-center font-editorial text-xl font-bold" aria-hidden="true">{{ mb_substr($author->name, 0, 1) }}</span>
                    <span class="min-w-0 flex-1">
                        <span class="block font-editorial text-lg font-bold text-editorial-heading group-hover:text-[#9A7845]">{{ $author->name }}</span>
                        <span class="block text-xs text-editorial-secondary mt-1">{{ trans_choice(':count quotation|:count quotations', $author->quotes_count, ['count' => $author->quotes_count]) }}</span>
                    </span>
                    <svg class="w-4 h-4 text-editorial-secondary mt-1 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6"/></svg>
                </a>
            @endforeach
        </section>
        <div>{{ $authors->links('vendor.pagination.editorial') }}</div>
    @endif
</div>
@endsection
