@php
    $locale = $currentLocale ?? app()->getLocale();
    $siteBrand = $brandName ?? config("brand.name.{$locale}", config('brand.technical_name', 'ATHAR'));
    $tagline = $brandTagline ?? config("brand.tagline.{$locale}", '');
@endphp

<footer class="mt-16 border-t border-editorial bg-editorial-card">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="flex flex-col sm:flex-row items-center gap-3 text-center sm:text-start">
            <span class="font-editorial font-bold text-base text-editorial-heading">
                {{ $siteBrand }}
            </span>
            <span class="hidden sm:inline text-editorial-secondary">•</span>
            <p class="text-xs text-editorial-secondary">
                {{ $tagline }}
            </p>
        </div>

        <div class="flex items-center gap-4 text-xs text-editorial-secondary">
            <a href="{{ route('locale.switch', 'ar') }}" class="hover:text-editorial-primary {{ $locale === 'ar' ? 'font-bold text-editorial-primary underline' : '' }}">
                العربية
            </a>
            <span>•</span>
            <a href="{{ route('locale.switch', 'en') }}" class="hover:text-editorial-primary {{ $locale === 'en' ? 'font-bold text-editorial-primary underline' : '' }}">
                English
            </a>
        </div>
    </div>
</footer>