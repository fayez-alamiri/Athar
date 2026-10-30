@extends('layouts.app')

@section('title', __('Sign In'))

@section('content')
<div class="max-w-md mx-auto px-4 sm:px-6 py-12">
    <div class="editorial-card p-7 sm:p-9 space-y-6">
        <div class="text-center space-y-2">
            <span class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-editorial-secondary border border-editorial text-[#4A3328] dark:text-[#C9A66B]">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
                </svg>
            </span>
            <p class="font-editorial text-sm font-bold text-[#B99A68] tracking-wide">
                {{ $brandName ?? config('brand.name.' . app()->getLocale(), 'ATHAR') }}
            </p>
            <h1 class="font-editorial text-2xl font-bold text-editorial-heading">
                {{ __('Sign In') }}
            </h1>
            <p class="text-sm text-editorial-secondary">
                {{ __('Sign in to publish quotes, like passages, and curate your saved collection.') }}
            </p>
        </div>

        <form method="POST" action="{{ route('login.store') }}" class="space-y-4" novalidate>
            @csrf

            <x-input
                name="email"
                type="email"
                :label="__('Email Address')"
                :placeholder="__('reader@example.com')"
                required
                autocomplete="email"
                autofocus
            />

            <x-input
                name="password"
                type="password"
                :label="__('Password')"
                :placeholder="__('Enter your password')"
                required
                autocomplete="current-password"
            />

            <div class="flex items-center justify-between pt-1">
                <label for="remember" class="inline-flex items-center gap-2 text-xs text-editorial-secondary cursor-pointer select-none">
                    <input
                        id="remember"
                        name="remember"
                        type="checkbox"
                        value="1"
                        class="rounded border-editorial text-[#4A3328] focus:ring-[#B99A68]"
                    >
                    <span>{{ __('Remember me') }}</span>
                </label>
            </div>

            <div class="pt-2">
                <x-button type="submit" variant="primary" class="w-full">
                    {{ __('Sign In') }}
                </x-button>
            </div>
        </form>

        <div class="pt-4 border-t border-editorial text-center text-xs text-editorial-secondary">
            <span>{{ __("Don't have an account?") }}</span>
            <a href="{{ route('register') }}" class="font-bold text-editorial-heading hover:underline ms-1">
                {{ __('Register') }}
            </a>
        </div>
    </div>
</div>
@endsection