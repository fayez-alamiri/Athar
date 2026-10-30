@php
    $locale = $currentLocale ?? app()->getLocale();
    $siteBrand = $brandName ?? config("brand.name.{$locale}", config('brand.technical_name', 'ATHAR'));
    $siteSubtitle = $brandSubtitle ?? config("brand.wordmark_subtitle.{$locale}", '');
@endphp

<div
    id="share-quote-modal"
    data-modal-backdrop
    aria-hidden="true"
    role="dialog"
    aria-modal="true"
    aria-labelledby="share-quote-modal-title"
    data-brand-name="{{ e($siteBrand) }}"
    data-brand-subtitle="{{ e($siteSubtitle) }}"
    data-label-quick="{{ __('Quick Quote') }}"
    data-label-book="{{ __('Book Quote') }}"
    data-label-published-by="{{ __('Published by') }}"
    data-label-attributed-to="{{ __('Attributed to') }}"
    data-label-from-book="{{ __('From book') }}"
    data-msg-downloaded="{{ __('PNG image downloaded successfully.') }}"
    data-msg-shared="{{ __('Quote image shared successfully.') }}"
    data-msg-share-unsupported="{{ __('Direct file sharing is not supported on this browser. Please use Download PNG or Copy Quote Text.') }}"
    data-msg-copied="{{ __('Copied to clipboard') }}"
    data-msg-copy-error="{{ __('Unable to copy quote') }}"
    class="hidden fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-5 bg-black/65 backdrop-blur-xs overflow-y-auto"
>
    <div class="editorial-card w-full max-w-4xl p-5 sm:p-7 relative my-auto max-h-[94vh] overflow-y-auto">
        {{-- Modal Header --}}
        <div class="flex items-center justify-between gap-4 pb-4 border-b border-editorial">
            <div>
                <h3 id="share-quote-modal-title" class="font-editorial text-lg sm:text-xl font-bold text-editorial-heading">
                    {{ __('Share Quote as PNG') }}
                </h3>
                <p class="text-xs text-editorial-secondary mt-0.5">
                    {{ __('Choose a visual template, preview the card, and download a high-resolution PNG image.') }}
                </p>
            </div>

            <button
                type="button"
                data-modal-close="share-quote-modal"
                aria-label="{{ __('Close') }}"
                class="w-8 h-8 rounded-lg flex items-center justify-center text-editorial-secondary hover:text-editorial-primary hover:bg-editorial-secondary transition-colors cursor-pointer shrink-0"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Modal Body: Live Preview Canvas + Template Selector & Actions --}}
        <div class="pt-5 grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

            {{-- Left / Start Column: High-DPI Canvas Preview --}}
            <div class="lg:col-span-7 flex flex-col items-center">
                <div class="w-full max-w-md aspect-square rounded-2xl overflow-hidden border border-editorial bg-editorial-secondary/50 shadow-md relative flex items-center justify-center">
                    <canvas
                        id="share-quote-canvas"
                        width="1080"
                        height="1080"
                        class="w-full h-full object-contain block"
                        aria-label="{{ __('Quote share image preview') }}"
                    ></canvas>

                    {{-- Rendering Indicator --}}
                    <div
                        id="share-canvas-loading"
                        class="hidden absolute inset-0 bg-black/35 backdrop-blur-xs flex items-center justify-center text-xs font-semibold text-white"
                    >
                        <span>{{ __('Rendering preview...') }}</span>
                    </div>
                </div>

                <p class="text-[11px] text-editorial-secondary mt-2 text-center">
                    {{ __('High-resolution PNG (1080 × 1080 px) — optimized for social sharing') }}
                </p>
            </div>

            {{-- Right / End Column: Template Picker & Export Controls --}}
            <div class="lg:col-span-5 space-y-5">

                {{-- Template Selection Grid --}}
                <div class="space-y-2.5">
                    <span class="block text-xs font-bold uppercase tracking-wider text-editorial-secondary">
                        {{ __('Select Design Template') }}
                    </span>

                    <div class="grid grid-cols-2 gap-2.5" role="radiogroup" aria-label="{{ __('Select Design Template') }}">
                        {{-- 1. Warm Literary Editorial (Default Brand Identity) --}}
                        <button
                            type="button"
                            data-share-template="editorial"
                            role="radio"
                            aria-checked="true"
                            class="group p-3 rounded-xl border text-start transition-all cursor-pointer border-[#4A3328] dark:border-[#C9A66B] bg-editorial-secondary/70"
                        >
                            <div class="flex items-center gap-2 mb-1.5">
                                <span class="w-4 h-4 rounded-full bg-[#F8F4EC] border border-[#4A3328] inline-block"></span>
                                <span class="w-4 h-4 rounded-full bg-[#4A3328] inline-block -ms-1.5"></span>
                                <span class="w-4 h-4 rounded-full bg-[#B99A68] inline-block -ms-1.5"></span>
                            </div>
                            <span class="block text-xs font-bold text-editorial-heading">
                                {{ __('Warm Editorial') }}
                            </span>
                            <span class="block text-[11px] text-editorial-secondary">
                                {{ __('Signature ivory & gold') }}
                            </span>
                        </button>

                        {{-- 2. Quiet Paper --}}
                        <button
                            type="button"
                            data-share-template="paper"
                            role="radio"
                            aria-checked="false"
                            class="group p-3 rounded-xl border text-start transition-all cursor-pointer border-editorial hover:border-[#B99A68] bg-editorial-card"
                        >
                            <div class="flex items-center gap-2 mb-1.5">
                                <span class="w-4 h-4 rounded-full bg-[#FFFEFA] border border-[#D2C2AC] inline-block"></span>
                                <span class="w-4 h-4 rounded-full bg-[#2B1D17] inline-block -ms-1.5"></span>
                                <span class="w-4 h-4 rounded-full bg-[#827469] inline-block -ms-1.5"></span>
                            </div>
                            <span class="block text-xs font-bold text-editorial-heading">
                                {{ __('Quiet Paper') }}
                            </span>
                            <span class="block text-[11px] text-editorial-secondary">
                                {{ __('Minimalist book page') }}
                            </span>
                        </button>

                        {{-- 3. Dark Elegant --}}
                        <button
                            type="button"
                            data-share-template="dark"
                            role="radio"
                            aria-checked="false"
                            class="group p-3 rounded-xl border text-start transition-all cursor-pointer border-editorial hover:border-[#B99A68] bg-editorial-card"
                        >
                            <div class="flex items-center gap-2 mb-1.5">
                                <span class="w-4 h-4 rounded-full bg-[#211A16] border border-[#56453B] inline-block"></span>
                                <span class="w-4 h-4 rounded-full bg-[#2C231D] inline-block -ms-1.5"></span>
                                <span class="w-4 h-4 rounded-full bg-[#C9A66B] inline-block -ms-1.5"></span>
                            </div>
                            <span class="block text-xs font-bold text-editorial-heading">
                                {{ __('Dark Elegant') }}
                            </span>
                            <span class="block text-[11px] text-editorial-secondary">
                                {{ __('Espresso night & gold') }}
                            </span>
                        </button>

                        {{-- 4. Literary Magazine --}}
                        <button
                            type="button"
                            data-share-template="magazine"
                            role="radio"
                            aria-checked="false"
                            class="group p-3 rounded-xl border text-start transition-all cursor-pointer border-editorial hover:border-[#B99A68] bg-editorial-card"
                        >
                            <div class="flex items-center gap-2 mb-1.5">
                                <span class="w-4 h-4 rounded-full bg-[#4A3328] border border-[#B99A68] inline-block"></span>
                                <span class="w-4 h-4 rounded-full bg-[#EFE5D5] inline-block -ms-1.5"></span>
                                <span class="w-4 h-4 rounded-full bg-[#2B1D17] inline-block -ms-1.5"></span>
                            </div>
                            <span class="block text-xs font-bold text-editorial-heading">
                                {{ __('Literary Magazine') }}
                            </span>
                            <span class="block text-[11px] text-editorial-secondary">
                                {{ __('Editorial masthead') }}
                            </span>
                        </button>
                    </div>
                </div>

                {{-- Primary Action Buttons: Download PNG, Native Share, Copy Text Fallback --}}
                <div class="space-y-2.5 pt-2 border-t border-editorial">
                    <button
                        type="button"
                        id="share-download-png-btn"
                        class="w-full px-4 py-3 rounded-xl text-sm font-semibold btn-editorial-primary flex items-center justify-center gap-2 cursor-pointer shadow-xs"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        <span>{{ __('Download PNG') }}</span>
                    </button>

                    <button
                        type="button"
                        id="share-native-file-btn"
                        class="w-full px-4 py-2.5 rounded-xl text-xs font-semibold btn-editorial-secondary flex items-center justify-center gap-2 cursor-pointer"
                    >
                        <svg class="w-4 h-4 text-[#B99A68]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
                        </svg>
                        <span>{{ __('Share Image via Device') }}</span>
                    </button>

                    <button
                        type="button"
                        id="share-copy-text-btn"
                        class="w-full px-4 py-2.5 rounded-xl text-xs font-semibold btn-editorial-secondary flex items-center justify-center gap-2 cursor-pointer"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                        <span>{{ __('Copy Quote Text') }}</span>
                    </button>
                </div>

                <p class="text-[11px] text-editorial-secondary leading-relaxed">
                    {{ __('Note: Direct image sharing depends on your browser and operating system. Download PNG and Copy Quote Text are always available.') }}
                </p>
            </div>
        </div>
    </div>
</div>