@extends('layouts.app')

@section('title', __('Register'))

@section('content')
<div class="max-w-md mx-auto px-4 sm:px-6 py-12">
    <div class="editorial-card p-7 sm:p-9 space-y-6">
        <div class="text-center space-y-2">
            <span class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-editorial-secondary border border-editorial text-[#4A3328] dark:text-[#C9A66B]">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </span>
            <p class="font-editorial text-sm font-bold text-[#B99A68] tracking-wide">
                {{ $brandName ?? config('brand.name.' . app()->getLocale(), 'ATHAR') }}
            </p>
            <h1 class="font-editorial text-2xl font-bold text-editorial-heading">
                {{ __('Create Your Account') }}
            </h1>
            <p class="text-sm text-editorial-secondary">
                {{ __('Join the literary community to share quotes from books and your own reflections.') }}
            </p>
        </div>

        <form method="POST" action="{{ route('register.store') }}" class="space-y-4" novalidate>
            @csrf

            <x-input
                name="name"
                type="text"
                :label="__('Full Name')"
                :placeholder="__('2 to 50 characters')"
                :hint="__('Between 2 and 50 characters.')"
                required
                minlength="2"
                maxlength="50"
                autocomplete="name"
                autofocus
            />

            <x-input
                name="email"
                type="email"
                :label="__('Email Address')"
                :placeholder="__('reader@example.com')"
                required
                autocomplete="email"
            />

            <x-input
                name="password"
                type="password"
                :label="__('Password')"
                :placeholder="__('At least 8 characters')"
                :hint="__('Minimum 8 characters.')"
                required
                minlength="8"
                autocomplete="new-password"
            />

            <x-input
                name="password_confirmation"
                type="password"
                :label="__('Confirm Password')"
                :placeholder="__('Re-enter your password')"
                required
                minlength="8"
                autocomplete="new-password"
            />

            <div class="pt-2">
                <x-button type="submit" variant="primary" class="w-full">
                    {{ __('Register') }}
                </x-button>
            </div>
        </form>

        <div class="pt-4 border-t border-editorial text-center text-xs text-editorial-secondary">
            <span>{{ __('Already have an account?') }}</span>
            <a href="{{ route('login') }}" class="font-bold text-editorial-heading hover:underline ms-1">
                {{ __('Sign In') }}
            </a>
        </div>
    </div>
</div>
@endsection