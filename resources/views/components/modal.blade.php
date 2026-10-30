@props([
    'id',
    'title' => '',
    'maxWidth' => 'max-w-lg',
])

<div
    id="{{ $id }}"
    data-modal-backdrop
    aria-hidden="true"
    role="dialog"
    aria-modal="true"
    aria-labelledby="{{ $id }}-title"
    class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs"
>
    <div class="editorial-card w-full {{ $maxWidth }} p-6 relative animate-in fade-in zoom-in-95 duration-150">
        <div class="flex items-center justify-between gap-4 pb-4 border-b border-editorial">
            <h3 id="{{ $id }}-title" class="font-editorial text-lg font-bold text-editorial-heading">
                {{ $title }}
            </h3>
            <button
                type="button"
                data-modal-close="{{ $id }}"
                aria-label="{{ __('Close') }}"
                class="w-8 h-8 rounded-lg flex items-center justify-center text-editorial-secondary hover:text-editorial-primary hover:bg-editorial-secondary transition-colors cursor-pointer"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div class="pt-4">
            {{ $slot }}
        </div>
    </div>
</div>