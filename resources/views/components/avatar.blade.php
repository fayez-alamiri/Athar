@props([
    'user' => null,
    'size' => 'md', // sm | md | lg | xl
])

@php
    $dimensions = match ($size) {
        'sm' => 'w-8 h-8 text-xs',
        'lg' => 'w-14 h-14 text-lg',
        'xl' => 'w-20 h-20 text-2xl',
        default => 'w-10 h-10 text-sm',
    };

    $name = $user?->name ?? 'Reader';
    $initials = collect(explode(' ', trim($name)))
        ->filter()
        ->map(fn ($part) => mb_substr($part, 0, 1))
        ->take(2)
        ->implode('');

    $avatarUrl = $user && method_exists($user, 'avatarUrl') ? $user->avatarUrl() : null;
@endphp

@if ($avatarUrl)
    <span class="inline-flex items-center justify-center shrink-0">
        <img
            src="{{ $avatarUrl }}"
            alt="{{ $name }}"
            class="{{ $dimensions }} rounded-full object-cover border border-editorial shrink-0"
            onerror="this.classList.add('hidden'); this.nextElementSibling.classList.remove('hidden');"
        >
        <span
            class="hidden {{ $dimensions }} rounded-full bg-[#4A3328] dark:bg-[#C9A66B] text-[#FFFEFA] dark:text-[#211A16] font-bold items-center justify-center select-none shrink-0"
            aria-hidden="true"
        >
            {{ $initials ?: 'Q' }}
        </span>
    </span>
@else
    <span
        class="{{ $dimensions }} rounded-full bg-[#4A3328] dark:bg-[#C9A66B] text-[#FFFEFA] dark:text-[#211A16] font-bold inline-flex items-center justify-center select-none shrink-0"
        aria-hidden="true"
    >
        {{ $initials ?: 'Q' }}
    </span>
@endif