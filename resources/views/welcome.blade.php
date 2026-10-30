@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-10">

    {{-- Editorial Hero Header --}}
    <section class="editorial-card p-8 sm:p-10 relative overflow-hidden">
        <div class="max-w-3xl space-y-4">
            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-editorial-secondary text-editorial-primary border border-editorial">
                <span class="w-2 h-2 rounded-full bg-[#B99A68]"></span>
                <span>{{ __('Editorial Reading Sanctuary') }}</span>
            </span>

            <h1 class="font-editorial text-3xl sm:text-4xl font-bold text-editorial-heading leading-tight">
                {{ $brandName ?? config('brand.name.' . app()->getLocale(), 'ATHAR') }}
            </h1>

            <p class="text-base sm:text-lg text-editorial-secondary leading-relaxed">
                {{ __('Curate, reflect on, and share timeless passages from books and fellow readers.') }}
            </p>

            <div class="pt-2 flex flex-wrap items-center gap-3">
                <x-button variant="primary" data-require-auth="publish-book">
                    {{ __('Add Book Quote') }}
                </x-button>
                <x-button variant="secondary" data-require-auth="publish-quick">
                    {{ __('Add Quick Quote') }}
                </x-button>
            </div>
        </div>
    </section>

    {{-- Sample Cards Demonstrating the Warm Literary Editorial Design System --}}
    <section class="grid grid-cols-1 md:grid-cols-2 gap-6">

        {{-- Sample Quick User Quote Card --}}
        <article class="editorial-card p-6 flex flex-col justify-between space-y-5">
            <div class="space-y-4">
                <div class="flex items-center justify-between gap-2">
                    <span class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-editorial-secondary text-editorial-primary">
                        {{ __('Sample Quick Quote') }}
                    </span>
                    <span class="text-xs text-editorial-secondary">2026</span>
                </div>

                <blockquote class="font-editorial text-xl leading-relaxed text-editorial-heading">
                    "القراءة تمنحنا حياة أخرى نعيشها فوق حياتنا، وتفتح لنا نوافذ لا تغلقها المسافات."
                </blockquote>
            </div>

            <div class="pt-4 border-t border-editorial flex items-center justify-between gap-4">
                <div class="text-xs text-editorial-secondary">
                    {{ __('Published by') }}: <strong class="text-editorial-primary">قارئ شغوف</strong>
                </div>

                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        data-copy-quote="القراءة تمنحنا حياة أخرى نعيشها فوق حياتنا، وتفتح لنا نوافذ لا تغلقها المسافات."
                        data-copy-author="قارئ شغوف"
                        data-copy-success="{{ __('Copied to clipboard') }}"
                        data-copy-error="{{ __('Unable to copy quote') }}"
                        class="px-3 py-1.5 rounded-xl text-xs font-semibold btn-editorial-secondary cursor-pointer"
                    >
                        {{ __('Copy') }}
                    </button>

                    <button
                        type="button"
                        data-require-auth="like"
                        class="px-3 py-1.5 rounded-xl text-xs font-semibold btn-editorial-secondary cursor-pointer"
                    >
                        ♥ {{ __('Like') }}
                    </button>

                    <button
                        type="button"
                        data-require-auth="save"
                        class="px-3 py-1.5 rounded-xl text-xs font-semibold btn-editorial-secondary cursor-pointer"
                    >
                        {{ __('Save') }}
                    </button>
                </div>
            </div>
        </article>

        {{-- Sample Book Quote Card --}}
        <article class="editorial-card p-6 flex flex-col justify-between space-y-5">
            <div class="space-y-4">
                <div class="flex items-center justify-between gap-2">
                    <span class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-[#B99A68]/20 text-editorial-primary border border-[#B99A68]/40">
                        {{ __('Sample Book Quote') }}
                    </span>
                    <span class="text-xs text-editorial-secondary">2026</span>
                </div>

                <blockquote class="font-editorial text-xl leading-relaxed text-editorial-heading" dir="ltr">
                    "A room without books is like a body without a soul."
                </blockquote>

                <div class="flex items-center gap-3 p-3 rounded-xl bg-editorial-secondary/60 border border-editorial">
                    <div class="w-10 h-14 rounded-lg bg-[#4A3328] text-[#FFFEFA] flex items-center justify-center text-xs font-bold shrink-0">
                        BOOK
                    </div>
                    <div class="text-xs space-y-0.5">
                        <p class="font-bold text-editorial-heading">{{ __('From book') }}: Classic Letters & Essays</p>
                        <p class="text-editorial-secondary">{{ __('Attributed to') }}: Marcus Tullius Cicero</p>
                    </div>
                </div>
            </div>

            <div class="pt-4 border-t border-editorial flex items-center justify-between gap-4">
                <div class="text-xs text-editorial-secondary">
                    {{ __('Published by') }}: <strong class="text-editorial-primary">Editorial Reader</strong>
                </div>

                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        data-copy-quote="A room without books is like a body without a soul."
                        data-copy-author="Marcus Tullius Cicero"
                        data-copy-success="{{ __('Copied to clipboard') }}"
                        data-copy-error="{{ __('Unable to copy quote') }}"
                        class="px-3 py-1.5 rounded-xl text-xs font-semibold btn-editorial-secondary cursor-pointer"
                    >
                        {{ __('Copy') }}
                    </button>

                    <button
                        type="button"
                        data-require-auth="like"
                        class="px-3 py-1.5 rounded-xl text-xs font-semibold btn-editorial-secondary cursor-pointer"
                    >
                        ♥ {{ __('Like') }}
                    </button>

                    <button
                        type="button"
                        data-require-auth="save"
                        class="px-3 py-1.5 rounded-xl text-xs font-semibold btn-editorial-secondary cursor-pointer"
                    >
                        {{ __('Save') }}
                    </button>
                </div>
            </div>
        </article>
    </section>

    {{-- Stage 1 Status Notice --}}
    <section class="editorial-card p-6 border-s-4 border-s-[#B99A68]">
        <h2 class="font-editorial text-base font-bold text-editorial-heading">
            {{ __('Design System & Application Shell Active') }}
        </h2>
        <p class="text-sm text-editorial-secondary mt-1">
            {{ __('All navigation links, RTL/LTR localization, and Warm Literary Editorial light/dark themes are active.') }}
            {{ __('Authentication and database features will be connected in Stage 2.') }}
        </p>
    </section>

</div>
@endsection