@props([
    'title' => __('No quotes found'),
    'description' => __('Try adjusting your search or filter, or publish the first quote in this space.'),
])

<div class="editorial-card p-10 text-center max-w-xl mx-auto my-6">
    <div class="w-12 h-12 rounded-2xl bg-editorial-secondary border border-editorial mx-auto flex items-center justify-center text-[#B99A68] mb-4">
        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
        </svg>
    </div>

    <h3 class="font-editorial text-lg font-bold text-editorial-heading">
        {{ $title }}
    </h3>
    <p class="text-sm text-editorial-secondary mt-1.5 leading-relaxed">
        {{ $description }}
    </p>

    @if ($slot->isNotEmpty())
        <div class="mt-5 flex items-center justify-center gap-3">
            {{ $slot }}
        </div>
    @endif
</div>