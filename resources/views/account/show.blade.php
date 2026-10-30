@extends('layouts.app')

@section('title', __('My Account'))

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10 space-y-8">

    {{-- Profile Header & Avatar Management Card --}}
    <section class="editorial-card p-6 sm:p-8 space-y-6">
        <div class="flex flex-col sm:flex-row items-center sm:items-start gap-6">
            {{-- Interactive Avatar Circle (Clicking opens file selector) --}}
            <button
                type="button"
                data-avatar-trigger
                title="{{ __('Profile Picture') }}"
                aria-label="{{ __('Profile Picture') }}"
                class="group relative shrink-0 rounded-full focus:outline-none focus:ring-2 focus:ring-[#B99A68] cursor-pointer"
            >
                @php
                    $avatarUrl = $user->avatarUrl();
                    $initials = collect(explode(' ', trim($user->name)))
                        ->filter()
                        ->map(fn ($part) => mb_substr($part, 0, 1))
                        ->take(2)
                        ->implode('');
                @endphp

                <span class="w-20 h-20 rounded-full overflow-hidden border-2 border-editorial group-hover:border-[#B99A68] transition-colors flex items-center justify-center theme-brand-pill font-bold text-2xl relative pointer-events-none">
                    <img
                        id="account-avatar-preview-img"
                        src="{{ $avatarUrl ?? '' }}"
                        alt="{{ $user->name }}"
                        class="{{ $avatarUrl ? '' : 'hidden' }} w-full h-full object-cover"
                        onerror="this.classList.add('hidden'); this.nextElementSibling.classList.remove('hidden');"
                    >
                    <span
                        data-avatar-initials
                        class="{{ $avatarUrl ? 'hidden' : '' }} select-none"
                    >
                        {{ $initials ?: 'Q' }}
                    </span>
                </span>
            </button>

            <div class="flex-1 text-center sm:text-start space-y-1.5">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-editorial-secondary text-editorial-primary border border-editorial">
                    {{ __('My Account') }}
                </span>
                <h1 class="font-editorial text-2xl sm:text-3xl font-bold text-editorial-heading">
                    {{ $user->name }}
                </h1>
                <p class="text-sm text-editorial-secondary">
                    {{ $user->email }}
                </p>
                <p class="text-xs text-editorial-secondary pt-1">
                    {{ __('Member since :date', ['date' => $user->created_at?->translatedFormat('F Y')]) }}
                </p>
            </div>

            <div class="flex flex-wrap items-center justify-center sm:justify-end gap-2 shrink-0">
                <x-button variant="secondary" size="sm" :href="route('my-quotes.index')">
                    {{ __('My Quotes') }}
                </x-button>
                <x-button variant="secondary" size="sm" :href="route('saved.index')">
                    {{ __('Saved Quotes') }}
                </x-button>
            </div>
        </div>

        {{-- Avatar Upload & Remove Controls --}}
        <div class="pt-6 border-t border-editorial grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
            <div class="space-y-1">
                <h2 class="font-editorial text-base font-bold text-editorial-heading">
                    {{ __('Profile Picture') }}
                </h2>
                <p class="text-xs text-editorial-secondary leading-relaxed">
                    {{ __('Upload a JPG, PNG, or WEBP image (maximum 2 MB). If no image is uploaded, your initials avatar is displayed.') }}
                </p>
                <p id="account-avatar-filename" class="hidden text-xs font-semibold text-[#B99A68] truncate pt-1"></p>
            </div>

            <div class="md:col-span-2 flex flex-col sm:flex-row items-stretch sm:items-center justify-end gap-3">
                <form
                    id="account-avatar-form"
                    method="POST"
                    action="{{ route('account.avatar.update', [], false) }}"
                    enctype="multipart/form-data"
                    class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 flex-1"
                    novalidate
                >
                    @csrf
                    <div class="flex-1">
                        <label for="avatar" class="sr-only">{{ __('Profile Picture') }}</label>
                        <input
                            id="avatar"
                            name="avatar"
                            type="file"
                            accept="image/jpeg,image/png,image/webp"
                            class="block w-full text-xs text-editorial-secondary file:me-3 file:py-2 file:px-3.5 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-[#4A3328] file:text-[#FFFEFA] hover:file:opacity-90 cursor-pointer input-editorial p-1.5 rounded-xl"
                        >
                    </div>
                    <x-button type="submit" variant="primary" size="sm">
                        {{ __('Update Avatar') }}
                    </x-button>
                </form>

                @if (! empty($user->avatar_path))
                    <form method="POST" action="{{ route('account.avatar.destroy', [], false) }}">
                        @csrf
                        @method('DELETE')
                        <x-button type="submit" variant="secondary" size="sm" class="text-red-700 dark:text-red-400">
                            {{ __('Remove Image') }}
                        </x-button>
                    </form>
                @endif
            </div>
        </div>

        @error('avatar')
            <p class="text-xs font-medium text-red-600 dark:text-red-400" role="alert">{{ $message }}</p>
        @enderror
    </section>

    {{-- Real Database User Statistics --}}
    <section aria-label="{{ __('Account Statistics') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        {{-- Published Quotes Count --}}
        <div class="editorial-card p-6 space-y-2">
            <div class="flex items-center justify-between text-editorial-secondary">
                <span class="text-xs font-semibold">{{ __('Published quotes') }}</span>
                <svg class="w-4 h-4 text-[#B99A68]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </div>
            <p class="font-editorial text-3xl font-bold text-editorial-heading" data-stat-published-quotes>
                {{ $stats['total_quotes'] }}
            </p>
            <p class="text-xs text-editorial-secondary">
                {{ __('Quick Quotes') }}: <strong>{{ $stats['quick_quotes'] }}</strong>
                <span class="mx-1">•</span>
                {{ __('Book Quotes') }}: <strong>{{ $stats['book_quotes'] }}</strong>
            </p>
        </div>

        {{-- Total Likes Received by User's Posts --}}
        <div class="editorial-card p-6 space-y-2">
            <div class="flex items-center justify-between text-editorial-secondary">
                <span class="text-xs font-semibold">{{ __('Likes received') }}</span>
                <svg class="w-4 h-4 text-[#B99A68]" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                    <path d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z"/>
                </svg>
            </div>
            <p class="font-editorial text-3xl font-bold text-editorial-heading" data-stat-received-likes>
                {{ $stats['received_likes'] }}
            </p>
            <p class="text-xs text-editorial-secondary">
                {{ __('Total likes received across all of your published quotes.') }}
            </p>
        </div>

        {{-- Saved Quotes Count --}}
        <div class="editorial-card p-6 space-y-2">
            <div class="flex items-center justify-between text-editorial-secondary">
                <span class="text-xs font-semibold">{{ __('Saved Quotes') }}</span>
                <svg class="w-4 h-4 text-[#B99A68]" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                </svg>
            </div>
            <p class="font-editorial text-3xl font-bold text-editorial-heading" data-stat-saved-quotes>
                {{ $stats['saved_quotes'] }}
            </p>
            <p class="text-xs text-editorial-secondary">
                {{ __('Quotes saved in your personal collection.') }}
            </p>
        </div>
    </section>

    {{-- Quick Navigation Cards to My Quotes & Saved Quotes --}}
    <section class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="editorial-card p-6 flex flex-col justify-between gap-4">
            <div class="space-y-1.5">
                <h2 class="font-editorial text-lg font-bold text-editorial-heading">
                    {{ __('Manage My Quotes') }}
                </h2>
                <p class="text-xs text-editorial-secondary leading-relaxed">
                    {{ __('View, filter, edit, or delete the quick quotes and book quotes you have published.') }}
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2 pt-2">
                <x-button variant="primary" size="sm" :href="route('my-quotes.index')">
                    {{ __('Open My Quotes') }}
                </x-button>
                <x-button variant="secondary" size="sm" :href="route('quotes.create-quick')">
                    {{ __('Add Quick Quote') }}
                </x-button>
                <x-button variant="secondary" size="sm" :href="route('quotes.create-book')">
                    {{ __('Add Book Quote') }}
                </x-button>
            </div>
        </div>

        <div class="editorial-card p-6 flex flex-col justify-between gap-4">
            <div class="space-y-1.5">
                <h2 class="font-editorial text-lg font-bold text-editorial-heading">
                    {{ __('Saved Quotes') }}
                </h2>
                <p class="text-xs text-editorial-secondary leading-relaxed">
                    {{ __('Browse the passages and reflections you have bookmarked from across the platform.') }}
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2 pt-2">
                <x-button variant="primary" size="sm" :href="route('saved.index')">
                    {{ __('Open Saved Quotes') }}
                </x-button>
                <x-button variant="secondary" size="sm" :href="route('home')">
                    {{ __('Explore Feed') }}
                </x-button>
            </div>
        </div>
    </section>

</div>
@endsection