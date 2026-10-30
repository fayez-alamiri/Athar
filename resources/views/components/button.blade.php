@props([
    'variant' => 'primary', // primary | secondary | danger | ghost
    'type' => 'button',
    'href' => null,
    'size' => 'md', // sm | md | lg
])

@php
    $baseClasses = 'inline-flex items-center justify-center gap-2 font-semibold rounded-xl transition-all focus:outline-none focus:ring-2 focus:ring-[#B99A68] cursor-pointer disabled:opacity-50 disabled:pointer-events-none';

    $sizeClasses = match ($size) {
        'sm' => 'px-3 py-1.5 text-xs',
        'lg' => 'px-6 py-3 text-base',
        default => 'px-4 py-2.5 text-sm',
    };

    $variantClasses = match ($variant) {
        'secondary' => 'btn-editorial-secondary',
        'danger' => 'bg-red-700 hover:bg-red-800 text-white border border-transparent',
        'ghost' => 'bg-transparent hover:bg-editorial-secondary text-editorial-secondary hover:text-editorial-primary',
        default => 'btn-editorial-primary',
    };

    $classes = "{$baseClasses} {$sizeClasses} {$variantClasses}";
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif