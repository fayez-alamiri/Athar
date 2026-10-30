@extends('layouts.app')

@section('title', __('Quote of the Day'))

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14 space-y-8">
    <section class="editorial-card p-7 sm:p-10 text-center space-y-3 relative overflow-hidden">
        <div class="absolute -top-20 start-1/2 -translate-x-1/2 w-64 h-64 rounded-full bg-[#B99A68]/10 blur-3xl pointer-events-none"></div>
        <p class="relative text-xs font-bold tracking-[0.18em] uppercase text-editorial-secondary">{{ __('A little inspiration for today') }}</p>
        <h1 class="relative font-editorial text-3xl sm:text-4xl font-bold text-editorial-heading">{{ __('Quote of the Day') }}</h1>
        <p class="relative text-sm text-editorial-secondary">{{ $today->locale(app()->getLocale())->translatedFormat('l, j F Y') }}</p>
    </section>

    @if ($quote)
        <div class="max-w-3xl mx-auto">
            <x-quote-card :quote="$quote" />
        </div>
        <div class="flex justify-center gap-3 flex-wrap">
            <x-button variant="secondary" :href="route('home')">{{ __('Explore all quotes') }}</x-button>
            <x-button variant="primary" :href="route('quotes.zen')">{{ __('Enter Zen Mode') }}</x-button>
        </div>
    @else
        <x-empty-state :title="__('No quote is available today')" :description="__('When the first quotation is published, it will be featured here automatically.')">
            @auth
                <x-button variant="primary" :href="route('quotes.create-quick')">{{ __('Publish a quote') }}</x-button>
            @else
                <x-button variant="primary" :href="route('register')">{{ __('Create an account') }}</x-button>
            @endauth
        </x-empty-state>
    @endif
</div>
@endsection
