@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex items-center justify-between gap-4 pt-6">
        {{-- Mobile Previous / Next --}}
        <div class="flex justify-between flex-1 sm:hidden gap-2">
            @if ($paginator->onFirstPage())
                <span class="px-4 py-2 text-xs font-semibold rounded-xl bg-editorial-secondary/50 text-editorial-secondary border border-editorial opacity-50 select-none">
                    {{ __('Previous') }}
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="px-4 py-2 text-xs font-semibold rounded-xl btn-editorial-secondary">
                    {{ __('Previous') }}
                </a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="px-4 py-2 text-xs font-semibold rounded-xl btn-editorial-secondary">
                    {{ __('Next') }}
                </a>
            @else
                <span class="px-4 py-2 text-xs font-semibold rounded-xl bg-editorial-secondary/50 text-editorial-secondary border border-editorial opacity-50 select-none">
                    {{ __('Next') }}
                </span>
            @endif
        </div>

        {{-- Desktop Summary & Page Numbers --}}
        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between gap-4">
            <div>
                <p class="text-xs text-editorial-secondary">
                    {{ __('Showing') }}
                    <span class="font-bold text-editorial-primary">{{ $paginator->firstItem() }}</span>
                    {{ __('to') }}
                    <span class="font-bold text-editorial-primary">{{ $paginator->lastItem() }}</span>
                    {{ __('of') }}
                    <span class="font-bold text-editorial-primary">{{ $paginator->total() }}</span>
                    {{ __('results') }}
                </p>
            </div>

            <div class="inline-flex items-center gap-1.5">
                {{-- Previous Page Link --}}
                @if ($paginator->onFirstPage())
                    <span aria-disabled="true" class="px-3 py-1.5 rounded-xl text-xs font-semibold bg-editorial-secondary/40 text-editorial-secondary border border-editorial opacity-50 select-none">
                        {{ __('Previous') }}
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="px-3 py-1.5 rounded-xl text-xs font-semibold btn-editorial-secondary">
                        {{ __('Previous') }}
                    </a>
                @endif

                {{-- Pagination Elements --}}
                @foreach ($elements as $element)
                    @if (is_string($element))
                        <span aria-disabled="true" class="px-2.5 py-1.5 text-xs text-editorial-secondary select-none">{{ $element }}</span>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span aria-current="page" class="px-3 py-1.5 rounded-xl text-xs font-bold bg-[#4A3328] text-[#FFFEFA] dark:bg-[#C9A66B] dark:text-[#211A16] shadow-xs">
                                    {{ $page }}
                                </span>
                            @else
                                <a href="{{ $url }}" class="px-3 py-1.5 rounded-xl text-xs font-semibold btn-editorial-secondary" aria-label="{{ __('Go to page :page', ['page' => $page]) }}">
                                    {{ $page }}
                                </a>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                {{-- Next Page Link --}}
                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="px-3 py-1.5 rounded-xl text-xs font-semibold btn-editorial-secondary">
                        {{ __('Next') }}
                    </a>
                @else
                    <span aria-disabled="true" class="px-3 py-1.5 rounded-xl text-xs font-semibold bg-editorial-secondary/40 text-editorial-secondary border border-editorial opacity-50 select-none">
                        {{ __('Next') }}
                    </span>
                @endif
            </div>
        </div>
    </nav>
@endif