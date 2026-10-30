@php
    $locale = $currentLocale ?? app()->getLocale();
    $siteBrand = $brandName ?? config("brand.name.{$locale}", config('brand.technical_name', 'ATHAR'));
    $siteSubtitle = $brandSubtitle ?? config("brand.wordmark_subtitle.{$locale}", '');
@endphp

<header class="sticky top-0 z-40 w-full bg-editorial-card/95 backdrop-blur-md border-b border-editorial">
    <nav class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 h-18 flex items-center justify-between gap-4" aria-label="Main Navigation">

        {{-- Brand Wordmark (Centralized in config/brand.php) --}}
        <div class="flex items-center gap-6">
            <a href="{{ route('home') }}" class="group flex items-center gap-3 focus:outline-none">
                <span class="w-10 h-10 rounded-xl theme-brand-pill flex items-center justify-center shadow-sm transition-transform group-hover:scale-105">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                </span>
                <div class="flex flex-col">
                    <span class="font-editorial text-xl font-bold tracking-tight text-editorial-heading leading-none">
                        {{ $siteBrand }}
                    </span>
                    @if($siteSubtitle)
                        <span class="text-[11px] text-editorial-secondary mt-1 hidden sm:inline-block">
                            {{ $siteSubtitle }}
                        </span>
                    @endif
                </div>
            </a>

            {{-- Primary Desktop Links --}}
            <div class="hidden md:flex items-center gap-1">
                <a
                    href="{{ route('home') }}"
                    class="px-3.5 py-2 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('home') || request()->routeIs('quotes.index') ? 'bg-editorial-secondary text-editorial-heading font-semibold' : 'text-editorial-secondary hover:text-editorial-heading hover:bg-editorial-secondary/50' }}"
                >
                    {{ __('Home') }}
                </a>
                <a href="{{ route('authors.index') }}" class="px-3.5 py-2 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('authors.*') ? 'bg-editorial-secondary text-editorial-heading font-semibold' : 'text-editorial-secondary hover:text-editorial-heading hover:bg-editorial-secondary/50' }}">{{ __('Authors') }}</a>
                <a href="{{ route('quotes.daily') }}" class="px-3.5 py-2 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('quotes.daily') ? 'bg-editorial-secondary text-editorial-heading font-semibold' : 'text-editorial-secondary hover:text-editorial-heading hover:bg-editorial-secondary/50' }}">{{ __('Daily') }}</a>
                <a href="{{ route('quotes.zen') }}" class="px-3.5 py-2 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('quotes.zen') ? 'bg-editorial-secondary text-editorial-heading font-semibold' : 'text-editorial-secondary hover:text-editorial-heading hover:bg-editorial-secondary/50' }}">{{ __('Zen') }}</a>

                @auth
                    <a
                        href="{{ Route::has('saved.index') ? route('saved.index') : '#' }}"
                        class="px-3.5 py-2 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('saved.*') ? 'bg-editorial-secondary text-editorial-heading font-semibold' : 'text-editorial-secondary hover:text-editorial-heading hover:bg-editorial-secondary/50' }}"
                    >
                        {{ __('Saved Quotes') }}
                    </a>

                    <a
                        href="{{ Route::has('my-quotes.index') ? route('my-quotes.index') : '#' }}"
                        class="px-3.5 py-2 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('my-quotes.*') ? 'bg-editorial-secondary text-editorial-heading font-semibold' : 'text-editorial-secondary hover:text-editorial-heading hover:bg-editorial-secondary/50' }}"
                    >
                        {{ __('My Quotes') }}
                    </a>
                @else
                    {{-- Guests clicking Saved Quotes get a clear Sign-In prompt rather than fake private data --}}
                    <button
                        type="button"
                        data-require-auth="saved"
                        class="px-3.5 py-2 rounded-xl text-sm font-medium text-editorial-secondary hover:text-editorial-heading hover:bg-editorial-secondary/50 transition-colors cursor-pointer"
                    >
                        {{ __('Saved Quotes') }}
                    </button>
                @endauth
            </div>
        </div>

        {{-- Right / End Controls: Publish (Auth), Language Switch (AR/EN), Theme Toggle (Sun/Moon), Auth/Account --}}
        <div class="flex items-center gap-2 sm:gap-3">

            {{-- Authenticated Quick & Book Quote Creation Shortcuts --}}
            @auth
                <div class="hidden lg:flex items-center gap-2">
                    <a
                        href="{{ Route::has('quotes.create-quick') ? route('quotes.create-quick') : '#' }}"
                        class="px-3 py-1.5 rounded-xl text-xs font-semibold btn-editorial-secondary flex items-center gap-1.5"
                    >
                        <svg class="w-3.5 h-3.5 text-editorial-accent pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>{{ __('Quick Quote') }}</span>
                    </a>

                    <a
                        href="{{ Route::has('quotes.create-book') ? route('quotes.create-book') : '#' }}"
                        class="px-3 py-1.5 rounded-xl text-xs font-semibold btn-editorial-primary flex items-center gap-1.5"
                    >
                        <svg class="w-3.5 h-3.5 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                        <span>{{ __('Book Quote') }}</span>
                    </a>
                </div>
            @endauth

            {{-- Compact AR / EN Language Switch --}}
            <div class="inline-flex items-center rounded-xl p-0.5 bg-editorial-secondary border border-editorial" role="group" aria-label="Language switch">
                <a
                    href="{{ route('locale.switch', 'ar') }}"
                    aria-label="{{ __('Switch to Arabic') }}"
                    class="px-2.5 py-1 rounded-lg text-xs font-bold tracking-wide transition-all {{ $locale === 'ar' ? 'theme-brand-pill shadow-xs' : 'text-editorial-secondary hover:text-editorial-primary' }}"
                >
                    AR
                </a>
                <a
                    href="{{ route('locale.switch', 'en') }}"
                    aria-label="{{ __('Switch to English') }}"
                    class="px-2.5 py-1 rounded-lg text-xs font-bold tracking-wide transition-all {{ $locale === 'en' ? 'theme-brand-pill shadow-xs' : 'text-editorial-secondary hover:text-editorial-primary' }}"
                >
                    EN
                </a>
            </div>

            {{-- Icon-Only Sun / Moon Theme Switch (Sun in Light Mode, Moon in Dark Mode) --}}
            <button
                type="button"
                data-theme-toggle
                data-label-light="{{ __('Light mode active — switch to dark mode') }}"
                data-label-dark="{{ __('Dark mode active — switch to light mode') }}"
                aria-label="{{ __('Light mode active — switch to dark mode') }}"
                title="{{ __('Light mode active — switch to dark mode') }}"
                class="w-9 h-9 rounded-xl bg-editorial-secondary border border-editorial flex items-center justify-center text-editorial-primary hover:border-[#B99A68] transition-colors cursor-pointer"
            >
                {{-- Sun Icon: Visible when Light Mode is active --}}
                <svg data-theme-icon="sun" class="w-4 h-4 text-[#4A3328] pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <circle cx="12" cy="12" r="4"></circle>
                    <path stroke-linecap="round" d="M12 2v2m0 16v2M4.93 4.93l1.41 1.41m11.32 11.32l1.41 1.41M2 12h2m16 0h2M4.93 19.07l1.41-1.41m11.32-11.32l1.41-1.41"></path>
                </svg>

                {{-- Moon Icon: Visible when Dark Mode is active --}}
                <svg data-theme-icon="moon" class="w-4 h-4 text-[#C9A66B] hidden pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"></path>
                </svg>
            </button>

            {{-- Authenticated User Avatar Dropdown + Explicit Navbar Log Out Button --}}
            @auth
                @php
                    $authUser = Auth::user();
                    $receivedLikesCount = method_exists($authUser, 'receivedLikesCount') ? $authUser->receivedLikesCount() : 0;
                @endphp

                <div class="relative">
                    <button
                        type="button"
                        id="account-menu-button"
                        aria-expanded="false"
                        aria-haspopup="true"
                        aria-controls="account-menu-dropdown"
                        aria-label="{{ __('Account menu') }}"
                        title="{{ __('Account menu') }}"
                        class="flex items-center gap-1.5 p-1 rounded-xl border border-editorial hover:border-[#B99A68] transition-colors cursor-pointer focus:outline-none focus:ring-2 focus:ring-[#B99A68]"
                    >
                        <span class="pointer-events-none inline-flex items-center">
                            <x-avatar :user="$authUser" size="sm" />
                        </span>
                        <svg class="w-3.5 h-3.5 text-editorial-secondary pointer-events-none hidden sm:block" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <div
                        id="account-menu-dropdown"
                        class="hidden absolute end-0 mt-2 w-64 rounded-2xl bg-editorial-card border border-editorial shadow-xl py-2 z-50"
                        role="menu"
                    >
                        <div class="px-4 py-3 border-b border-editorial">
                            <p class="text-sm font-bold text-editorial-heading truncate">{{ $authUser->name }}</p>
                            <p class="text-xs text-editorial-secondary truncate">{{ $authUser->email }}</p>
                            <div class="mt-2 inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-editorial-secondary text-xs font-medium text-editorial-primary">
                                <svg class="w-3.5 h-3.5 text-[#B99A68]" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                    <path d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z"/>
                                </svg>
                                <span>{{ __('Likes received') }}: <strong data-navbar-likes-received>{{ $receivedLikesCount }}</strong></span>
                            </div>
                        </div>

                        <div class="py-1">
                            <a
                                href="{{ Route::has('account.show') ? route('account.show') : '#' }}"
                                class="block px-4 py-2 text-sm text-editorial-primary hover:bg-editorial-secondary transition-colors"
                                role="menuitem"
                            >
                                {{ __('My Account') }}
                            </a>
                            <a
                                href="{{ Route::has('my-quotes.index') ? route('my-quotes.index') : '#' }}"
                                class="block px-4 py-2 text-sm text-editorial-primary hover:bg-editorial-secondary transition-colors"
                                role="menuitem"
                            >
                                {{ __('My Quotes') }}
                            </a>
                            <a
                                href="{{ Route::has('saved.index') ? route('saved.index') : '#' }}"
                                class="block px-4 py-2 text-sm text-editorial-primary hover:bg-editorial-secondary transition-colors"
                                role="menuitem"
                            >
                                {{ __('Saved Quotes') }}
                            </a>
                        </div>

                        @if(Route::has('logout'))
                            <div class="border-t border-editorial pt-1 mt-1">
                                <form method="POST" action="{{ route('logout', [], false) }}">
                                    @csrf
                                    <button
                                        type="submit"
                                        class="w-full text-start px-4 py-2 text-sm font-medium text-red-700 dark:text-red-400 hover:bg-editorial-secondary transition-colors cursor-pointer"
                                        role="menuitem"
                                    >
                                        {{ __('Log Out') }}
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Separate Visible Log Out Control in Main Navigation (Required by Section 11 & 12) --}}
                @if(Route::has('logout'))
                    <form method="POST" action="{{ route('logout', [], false) }}" class="hidden sm:inline-block">
                        @csrf
                        <button
                            type="submit"
                            class="px-3 py-1.5 rounded-xl text-xs font-semibold btn-editorial-secondary hover:text-red-700 dark:hover:text-red-400 cursor-pointer"
                        >
                            {{ __('Log Out') }}
                        </button>
                    </form>
                @endif
            @else
                {{-- Guest Sign In & Register Controls --}}
                <div class="hidden sm:flex items-center gap-2">
                    <a
                        href="{{ Route::has('login') ? route('login') : '/login' }}"
                        class="px-3.5 py-2 rounded-xl text-xs font-semibold btn-editorial-secondary"
                    >
                        {{ __('Sign In') }}
                    </a>
                    <a
                        href="{{ Route::has('register') ? route('register') : '/register' }}"
                        class="px-3.5 py-2 rounded-xl text-xs font-semibold btn-editorial-primary"
                    >
                        {{ __('Register') }}
                    </a>
                </div>
            @endauth

            {{-- Mobile Hamburger Button --}}
            <button
                type="button"
                id="mobile-menu-toggle"
                aria-expanded="false"
                aria-controls="mobile-menu-panel"
                aria-label="{{ __('Open navigation menu') }}"
                class="md:hidden w-9 h-9 rounded-xl bg-editorial-secondary border border-editorial flex items-center justify-center text-editorial-primary cursor-pointer"
            >
                <svg class="w-5 h-5 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
        </div>
    </nav>

    {{-- Responsive Mobile Navigation Drawer --}}
    <div id="mobile-menu-panel" class="hidden md:hidden border-t border-editorial bg-editorial-card px-4 pt-3 pb-4 space-y-2">
        <a
            href="{{ route('home') }}"
            class="block px-3 py-2 rounded-xl text-sm font-medium {{ request()->routeIs('home') ? 'bg-editorial-secondary text-editorial-heading font-semibold' : 'text-editorial-secondary' }}"
        >
            {{ __('Home') }}
        </a>
        <a href="{{ route('authors.index') }}" class="block px-3 py-2 rounded-xl text-sm font-medium text-editorial-secondary hover:bg-editorial-secondary">{{ __('Authors') }}</a>
        <a href="{{ route('quotes.daily') }}" class="block px-3 py-2 rounded-xl text-sm font-medium text-editorial-secondary hover:bg-editorial-secondary">{{ __('Daily Quote') }}</a>
        <a href="{{ route('quotes.zen') }}" class="block px-3 py-2 rounded-xl text-sm font-medium text-editorial-secondary hover:bg-editorial-secondary">{{ __('Zen Mode') }}</a>

        @auth
            <a
                href="{{ Route::has('saved.index') ? route('saved.index') : '#' }}"
                class="block px-3 py-2 rounded-xl text-sm font-medium text-editorial-secondary hover:bg-editorial-secondary"
            >
                {{ __('Saved Quotes') }}
            </a>
            <a
                href="{{ Route::has('my-quotes.index') ? route('my-quotes.index') : '#' }}"
                class="block px-3 py-2 rounded-xl text-sm font-medium text-editorial-secondary hover:bg-editorial-secondary"
            >
                {{ __('My Quotes') }}
            </a>
            <a
                href="{{ Route::has('account.show') ? route('account.show') : '#' }}"
                class="block px-3 py-2 rounded-xl text-sm font-medium text-editorial-secondary hover:bg-editorial-secondary"
            >
                {{ __('My Account') }}
            </a>
            <div class="pt-2 grid grid-cols-2 gap-2">
                <a
                    href="{{ Route::has('quotes.create-quick') ? route('quotes.create-quick') : '#' }}"
                    class="px-3 py-2 rounded-xl text-xs font-semibold text-center btn-editorial-secondary"
                >
                    {{ __('Add Quick Quote') }}
                </a>
                <a
                    href="{{ Route::has('quotes.create-book') ? route('quotes.create-book') : '#' }}"
                    class="px-3 py-2 rounded-xl text-xs font-semibold text-center btn-editorial-primary"
                >
                    {{ __('Add Book Quote') }}
                </a>
            </div>
            @if(Route::has('logout'))
                <form method="POST" action="{{ route('logout', [], false) }}" class="pt-2">
                    @csrf
                    <button type="submit" class="w-full px-3 py-2 rounded-xl text-xs font-semibold text-center btn-editorial-secondary text-red-700 dark:text-red-400 cursor-pointer">
                        {{ __('Log Out') }}
                    </button>
                </form>
            @endif
        @else
            <button
                type="button"
                data-require-auth="saved"
                class="w-full text-start px-3 py-2 rounded-xl text-sm font-medium text-editorial-secondary hover:bg-editorial-secondary cursor-pointer"
            >
                {{ __('Saved Quotes') }}
            </button>
            <div class="pt-2 grid grid-cols-2 gap-2">
                <a
                    href="{{ Route::has('login') ? route('login') : '/login' }}"
                    class="px-3 py-2 rounded-xl text-xs font-semibold text-center btn-editorial-secondary"
                >
                    {{ __('Sign In') }}
                </a>
                <a
                    href="{{ Route::has('register') ? route('register') : '/register' }}"
                    class="px-3 py-2 rounded-xl text-xs font-semibold text-center btn-editorial-primary"
                >
                    {{ __('Register') }}
                </a>
            </div>
        @endauth
    </div>
</header>