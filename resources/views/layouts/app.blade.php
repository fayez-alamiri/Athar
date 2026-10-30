<!DOCTYPE html>
<html
    lang="{{ $currentLocale ?? app()->getLocale() }}"
    dir="{{ $textDirection ?? (app()->getLocale() === 'ar' ? 'rtl' : 'ltr') }}"
    class="antialiased"
>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>
        @hasSection('title')
            @yield('title') — {{ $brandName ?? config('brand.name.' . app()->getLocale(), 'ATHAR') }}
        @else
            {{ $brandName ?? config('brand.name.' . app()->getLocale(), 'ATHAR') }} — {{ $brandSubtitle ?? config('brand.wordmark_subtitle.' . app()->getLocale(), '') }}
        @endif
    </title>

    {{-- Website Favicon & Apple Touch Icon --}}
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}?v=2">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('favicon.ico') }}?v=2">

    {{-- Early no-flicker theme initialization (respects saved choice, falls back to OS preference on first visit) --}}
    <script>
        (function () {
            try {
                var saved = localStorage.getItem('quoteit_theme');
                var prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
                var theme = (saved === 'dark' || saved === 'light') ? saved : (prefersDark ? 'dark' : 'light');
                if (theme === 'dark') {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
                document.documentElement.setAttribute('data-theme', theme);
                document.documentElement.style.colorScheme = theme;
            } catch (e) {}
        })();
    </script>

    {{-- Critical Theme & Icon CSS Rules --}}
    <style>
        :root, html:not(.dark) {
            --bg-main: #F8F4EC;
            --bg-card: #FFFEFA;
            --bg-secondary: #EFE5D5;
            --bg-elevated: #F5EFE4;
            --text-primary: #2B1D17;
            --text-heading: #2B1D17;
            --text-secondary: #827469;
            --brand-primary: #4A3328;
            --brand-primary-hover: #3B281F;
            --brand-contrast: #FFFEFA;
            --accent-gold: #B99A68;
            --accent-gold-soft: rgba(185, 154, 104, 0.16);
            --border-subtle: #E4D9C8;
            --border-strong: #D2C2AC;
            --shadow-card: 0 6px 24px -6px rgba(43, 29, 23, 0.06), 0 2px 6px -1px rgba(43, 29, 23, 0.04);
            color-scheme: light;
        }
        html.dark {
            --bg-main: #211A16;
            --bg-card: #2C231D;
            --bg-secondary: #362B24;
            --bg-elevated: #261E19;
            --text-primary: #F2E8DA;
            --text-heading: #F2E8DA;
            --text-secondary: #C3B4A0;
            --brand-primary: #C9A66B;
            --brand-primary-hover: #D6B782;
            --brand-contrast: #211A16;
            --accent-gold: #C9A66B;
            --accent-gold-soft: rgba(201, 166, 107, 0.18);
            --border-subtle: #41342C;
            --border-strong: #56453B;
            --shadow-card: 0 8px 28px -6px rgba(0, 0, 0, 0.35), 0 2px 8px -2px rgba(0, 0, 0, 0.25);
            color-scheme: dark;
        }
        html, body {
            background-color: var(--bg-main) !important;
            color: var(--text-primary) !important;
        }
        .bg-editorial-main { background-color: var(--bg-main) !important; }
        .bg-editorial-card, .editorial-card { background-color: var(--bg-card) !important; }
        .bg-editorial-secondary { background-color: var(--bg-secondary) !important; }
        .text-editorial-primary { color: var(--text-primary) !important; }
        .text-editorial-heading { color: var(--text-heading) !important; }
        .text-editorial-secondary { color: var(--text-secondary) !important; }
        .border-editorial { border-color: var(--border-subtle) !important; }
        .btn-editorial-primary, .theme-brand-pill {
            background-color: var(--brand-primary) !important;
            color: var(--brand-contrast) !important;
        }
        .btn-editorial-secondary {
            background-color: var(--bg-secondary) !important;
            color: var(--text-primary) !important;
            border: 1px solid var(--border-subtle) !important;
        }
        .input-editorial {
            background-color: var(--bg-card) !important;
            color: var(--text-primary) !important;
            border: 1px solid var(--border-strong) !important;
        }
        html:not(.dark) [data-theme-icon="sun"] { display: block !important; }
        html:not(.dark) [data-theme-icon="moon"] { display: none !important; }
        html.dark [data-theme-icon="sun"] { display: none !important; }
        html.dark [data-theme-icon="moon"] { display: block !important; }
    </style>

    {{-- Literary Typography: Tajawal (Arabic), Lora (English literary serif), Inter (English UI) with safe fallbacks --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Lora:ital,wght@0,500;0,600;0,700;1,400&family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex flex-col bg-editorial-main text-editorial-primary selection:bg-[#B99A68]/30">

    {{-- Shared Navigation Header --}}
    @unless (request()->routeIs('quotes.zen'))
        @include('partials.navbar')
    @endunless

    {{-- Flash Status / Notification Banners --}}
    @if (session('status') || session('success') || session('error'))
        <div class="max-w-6xl mx-auto w-full px-4 sm:px-6 lg:px-8 pt-4">
            @if (session('status') || session('success'))
                <div role="alert" class="rounded-xl border border-[#B99A68] bg-editorial-card px-4 py-3 text-sm font-medium text-editorial-primary shadow-sm flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#B99A68] shrink-0"></span>
                        <span>{{ session('status') ?? session('success') }}</span>
                    </div>
                </div>
            @endif

            @if (session('error'))
                <div role="alert" class="rounded-xl border border-red-400/70 bg-red-50 dark:bg-red-950/40 px-4 py-3 text-sm font-medium text-red-800 dark:text-red-200 shadow-sm flex items-center justify-between gap-3 mt-2">
                    <span>{{ session('error') }}</span>
                </div>
            @endif
        </div>
    @endif

    {{-- Main Page Content --}}
    <main class="flex-1 w-full">
        @yield('content')
    </main>

    {{-- Shared Editorial Footer --}}
    @unless (request()->routeIs('quotes.zen'))
        @include('partials.footer')
    @endunless

    {{-- Guest Sign-In / Register Prompt Modal --}}
    <x-modal id="guest-auth-modal" :title="__('Sign in required')">
        <div class="space-y-4">
            <p class="text-sm leading-relaxed text-editorial-secondary">
                {{ __('Please sign in or create an account to like quotes, save them to your collection, or publish your own quotes.') }}
            </p>
            <div class="flex flex-wrap items-center justify-end gap-3 pt-2">
                <button
                    type="button"
                    data-modal-close="guest-auth-modal"
                    class="px-4 py-2 rounded-xl text-sm font-medium btn-editorial-secondary cursor-pointer"
                >
                    {{ __('Continue Browsing') }}
                </button>
                <a
                    href="{{ Route::has('login') ? route('login') : '/login' }}"
                    class="px-4 py-2 rounded-xl text-sm font-medium btn-editorial-secondary"
                >
                    {{ __('Sign In') }}
                </a>
                <a
                    href="{{ Route::has('register') ? route('register') : '/register' }}"
                    class="px-4 py-2 rounded-xl text-sm font-medium btn-editorial-primary"
                >
                    {{ __('Register') }}
                </a>
            </div>
        </div>
    </x-modal>

    {{-- Shared PNG Quote Share Generator Modal --}}
    @include('partials.share-modal')

    {{-- Floating Toast Container --}}
    <div
        id="quoteit-toast-container"
        class="fixed bottom-5 end-5 z-50 flex flex-col gap-2 pointer-events-none max-w-sm w-full px-4 sm:px-0"
        aria-live="polite"
    ></div>

    @stack('modals')
    @stack('scripts')

    {{-- Complete Self-Contained Application, Typewriter & PNG Share Generator Bootstrapper --}}
    <script>
        (function () {
            function initTypewriterElements() {
                var prefersReducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                if (prefersReducedMotion) return;

                document.querySelectorAll('[data-typewriter]').forEach(function (el) {
                    if (el.dataset.typewriterInitialized === 'true') return;
                    el.dataset.typewriterInitialized = 'true';

                    var fullText = (el.getAttribute('data-typewriter') || el.textContent || '').trim();
                    if (!fullText) return;

                    // Segment by grapheme clusters (or code points) so Arabic base letters + diacritics stay together
                    var segments = [];
                    if (typeof Intl !== 'undefined' && Intl.Segmenter) {
                        var segmenter = new Intl.Segmenter(document.documentElement.lang || 'ar', { granularity: 'grapheme' });
                        Array.from(segmenter.segment(fullText)).forEach(function (item) {
                            segments.push(item.segment);
                        });
                    } else {
                        segments = Array.from(fullText);
                    }

                    el.textContent = '';
                    var idx = 0;
                    var interval = setInterval(function () {
                        idx++;
                        el.textContent = segments.slice(0, idx).join('');
                        if (idx >= segments.length) {
                            clearInterval(interval);
                        }
                    }, 75);
                });
            }

            function bootQuoteItFallback() {
                initTypewriterElements();

                if (!window.__QUOTEIT_BOOTSTRAPPED__) {
                    window.__QUOTEIT_BOOTSTRAPPED__ = true;

                    var STORAGE_THEME_KEY = 'quoteit_theme';

                    function getCurrentTheme() {
                        return document.documentElement.classList.contains('dark') ? 'dark' : 'light';
                    }

                    function applyTheme(theme, persist) {
                        var root = document.documentElement;
                        var isDark = theme === 'dark';

                        root.classList.toggle('dark', isDark);
                        root.setAttribute('data-theme', isDark ? 'dark' : 'light');
                        root.style.colorScheme = isDark ? 'dark' : 'light';

                        if (persist) {
                            try {
                                localStorage.setItem(STORAGE_THEME_KEY, isDark ? 'dark' : 'light');
                            } catch (e) {}
                        }

                        document.querySelectorAll('[data-theme-toggle]').forEach(function (btn) {
                            var lightLabel = btn.getAttribute('data-label-light') || 'Light mode active — switch to dark mode';
                            var darkLabel = btn.getAttribute('data-label-dark') || 'Dark mode active — switch to light mode';
                            var label = isDark ? darkLabel : lightLabel;

                            btn.setAttribute('aria-label', label);
                            btn.setAttribute('title', label);
                            btn.setAttribute('aria-pressed', isDark ? 'true' : 'false');

                            var sunIcon = btn.querySelector('[data-theme-icon="sun"]');
                            var moonIcon = btn.querySelector('[data-theme-icon="moon"]');
                            if (sunIcon && moonIcon) {
                                sunIcon.classList.toggle('hidden', isDark);
                                moonIcon.classList.toggle('hidden', !isDark);
                            }
                        });
                    }

                    function toggleTheme() {
                        var next = getCurrentTheme() === 'dark' ? 'light' : 'dark';
                        applyTheme(next, true);
                    }

                    function showToast(message, type) {
                        var container = document.getElementById('quoteit-toast-container');
                        if (!container || !message) return;
                        var isError = type === 'error';
                        var toast = document.createElement('div');
                        toast.className = 'pointer-events-auto flex items-center gap-3 px-4 py-3 rounded-xl border shadow-lg text-sm font-medium transition-all duration-200 ' +
                            (isError ? 'bg-red-950/90 text-red-100 border-red-700' : 'bg-[#2B1D17] text-[#F8F4EC] border-[#B99A68]');
                        toast.setAttribute('role', 'status');
                        var dot = document.createElement('span');
                        dot.className = 'w-2 h-2 rounded-full shrink-0 ' + (isError ? 'bg-red-400' : 'bg-[#B99A68]');
                        var span = document.createElement('span');
                        span.textContent = message;
                        toast.appendChild(dot);
                        toast.appendChild(span);
                        container.appendChild(toast);
                        setTimeout(function () {
                            toast.style.opacity = '0';
                            setTimeout(function () { toast.remove(); }, 220);
                        }, 3000);
                    }

                    async function copyQuoteText(text, successMsg, errorMsg) {
                        if (!text) return false;
                        try {
                            if (navigator.clipboard && window.isSecureContext) {
                                await navigator.clipboard.writeText(text);
                                showToast(successMsg || 'Copied', 'success');
                                return true;
                            }
                        } catch (e) {}
                        try {
                            var ta = document.createElement('textarea');
                            ta.value = text;
                            ta.setAttribute('readonly', '');
                            ta.style.position = 'fixed';
                            ta.style.top = '0';
                            ta.style.opacity = '0';
                            document.body.appendChild(ta);
                            ta.select();
                            var ok = document.execCommand('copy');
                            document.body.removeChild(ta);
                            if (ok) {
                                showToast(successMsg || 'Copied', 'success');
                                return true;
                            }
                        } catch (e) {}
                        showToast(errorMsg || 'Unable to copy', 'error');
                        return false;
                    }

                    function openModal(modalId) {
                        var modal = document.getElementById(modalId);
                        if (!modal) return;
                        modal.classList.remove('hidden');
                        modal.setAttribute('aria-hidden', 'false');
                        document.body.classList.add('overflow-hidden');
                    }

                    function closeModal(modalId) {
                        var modal = document.getElementById(modalId);
                        if (!modal) return;
                        modal.classList.add('hidden');
                        modal.setAttribute('aria-hidden', 'true');
                        document.body.classList.remove('overflow-hidden');
                    }

                    function promptGuestAuth() {
                        openModal('guest-auth-modal');
                    }

                    function resolveSameOriginAction(actionUrl) {
                        try {
                            var parsed = new URL(actionUrl, window.location.origin);
                            return parsed.pathname + parsed.search;
                        } catch (e) {
                            return actionUrl;
                        }
                    }

                    function getCsrfToken(form) {
                        var input = form ? form.querySelector('input[name="_token"]') : null;
                        if (input && input.value) return input.value;
                        var meta = document.querySelector('meta[name="csrf-token"]');
                        return meta ? (meta.getAttribute('content') || '') : '';
                    }

                    async function handleLikeFormSubmit(form, ev) {
                        if (ev) {
                            ev.preventDefault();
                            ev.stopPropagation();
                        }
                        if (!form || form.dataset.submitting === 'true') return;

                        var quoteId = form.getAttribute('data-like-form');
                        var button = form.querySelector('[data-like-button]');
                        form.dataset.submitting = 'true';
                        if (button) button.classList.add('opacity-75', 'pointer-events-none');

                        try {
                            var csrf = getCsrfToken(form);
                            var formData = new FormData(form);
                            if (csrf && !formData.has('_token')) formData.append('_token', csrf);

                            var response = await fetch(resolveSameOriginAction(form.action), {
                                method: 'POST',
                                credentials: 'same-origin',
                                headers: {
                                    'X-CSRF-TOKEN': csrf,
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest'
                                },
                                body: formData
                            });

                            if (response.status === 401 || response.status === 419) {
                                promptGuestAuth();
                                return;
                            }
                            if (!response.ok) throw new Error('HTTP ' + response.status);

                            var data = await response.json();
                            var isLiked = Boolean(data.liked);

                            document.querySelectorAll('[data-like-button="' + quoteId + '"]').forEach(function (btn) {
                                btn.setAttribute('aria-pressed', isLiked ? 'true' : 'false');
                                btn.className = 'px-3 py-1.5 rounded-xl text-xs font-semibold inline-flex items-center gap-1.5 transition-all cursor-pointer ' +
                                    (isLiked ? 'theme-brand-pill' : 'btn-editorial-secondary');
                            });

                            document.querySelectorAll('[data-like-icon="' + quoteId + '"]').forEach(function (icon) {
                                icon.classList.toggle('fill-current', isLiked);
                                icon.classList.toggle('fill-none', !isLiked);
                            });

                            document.querySelectorAll('[data-like-count="' + quoteId + '"]').forEach(function (cnt) {
                                cnt.textContent = String(data.likes_count ?? 0);
                            });

                            if (typeof data.received_likes_count === 'number') {
                                document.querySelectorAll('[data-navbar-likes-received], [data-stat-received-likes]').forEach(function (el) {
                                    el.textContent = String(data.received_likes_count);
                                });
                            }

                            if (data.message) showToast(data.message, 'success');
                        } catch (err) {
                            showToast(document.documentElement.lang === 'ar' ? 'تعذر تحديث الإعجاب حالياً.' : 'Unable to update like right now.', 'error');
                        } finally {
                            form.dataset.submitting = 'false';
                            if (button) button.classList.remove('opacity-75', 'pointer-events-none');
                        }
                    }

                    async function handleSaveFormSubmit(form, ev) {
                        if (ev) {
                            ev.preventDefault();
                            ev.stopPropagation();
                        }
                        if (!form || form.dataset.submitting === 'true') return;

                        var quoteId = form.getAttribute('data-save-form');
                        var button = form.querySelector('[data-save-button]');
                        form.dataset.submitting = 'true';
                        if (button) button.classList.add('opacity-75', 'pointer-events-none');

                        try {
                            var csrf = getCsrfToken(form);
                            var formData = new FormData(form);
                            if (csrf && !formData.has('_token')) formData.append('_token', csrf);

                            var response = await fetch(resolveSameOriginAction(form.action), {
                                method: 'POST',
                                credentials: 'same-origin',
                                headers: {
                                    'X-CSRF-TOKEN': csrf,
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest'
                                },
                                body: formData
                            });

                            if (response.status === 401 || response.status === 419) {
                                promptGuestAuth();
                                return;
                            }
                            if (!response.ok) throw new Error('HTTP ' + response.status);

                            var data = await response.json();
                            var isSaved = Boolean(data.saved);

                            document.querySelectorAll('[data-save-button="' + quoteId + '"]').forEach(function (btn) {
                                btn.setAttribute('aria-pressed', isSaved ? 'true' : 'false');
                                btn.className = 'px-3 py-1.5 rounded-xl text-xs font-semibold inline-flex items-center gap-1.5 transition-all cursor-pointer ' +
                                    (isSaved ? 'bg-[#B99A68] text-[#2B1D17]' : 'btn-editorial-secondary');
                            });

                            document.querySelectorAll('[data-save-icon="' + quoteId + '"]').forEach(function (icon) {
                                icon.classList.toggle('fill-current', isSaved);
                                icon.classList.toggle('fill-none', !isSaved);
                            });

                            document.querySelectorAll('[data-save-label="' + quoteId + '"]').forEach(function (lbl) {
                                lbl.textContent = data.label || (isSaved ? 'Saved' : 'Save');
                            });

                            var savedGrid = document.querySelector('[data-saved-page-grid]');
                            if (savedGrid && !isSaved) {
                                var card = document.getElementById('quote-card-' + quoteId);
                                if (card) {
                                    card.remove();
                                    if (savedGrid.querySelectorAll('[data-quote-card]').length === 0) {
                                        window.location.reload();
                                    }
                                }
                            }

                            if (data.message) showToast(data.message, 'success');
                        } catch (err) {
                            showToast(document.documentElement.lang === 'ar' ? 'تعذر تحديث الحفظ حالياً.' : 'Unable to update saved state right now.', 'error');
                        } finally {
                            form.dataset.submitting = 'false';
                            if (button) button.classList.remove('opacity-75', 'pointer-events-none');
                        }
                    }

                    var avatarInput = document.getElementById('avatar');
                    var avatarForm = document.getElementById('account-avatar-form');
                    var previewImg = document.getElementById('account-avatar-preview-img');
                    var filenameLabel = document.getElementById('account-avatar-filename');
                    var pendingAutoSubmit = false;

                    if (avatarInput) {
                        avatarInput.addEventListener('change', function (e) {
                            var file = e.target.files && e.target.files[0];
                            if (!file) return;
                            if (filenameLabel) {
                                filenameLabel.textContent = file.name;
                                filenameLabel.classList.remove('hidden');
                            }
                            if (previewImg) {
                                var reader = new FileReader();
                                reader.onload = function (ev) {
                                    previewImg.src = ev.target.result;
                                    previewImg.classList.remove('hidden');
                                    var initials = previewImg.parentElement ? previewImg.parentElement.querySelector('[data-avatar-initials]') : null;
                                    if (initials) initials.classList.add('hidden');
                                };
                                reader.readAsDataURL(file);
                            }
                            if (pendingAutoSubmit && avatarForm) {
                                pendingAutoSubmit = false;
                                avatarForm.submit();
                            }
                        });
                    }

                    if (avatarForm && avatarInput) {
                        avatarForm.addEventListener('submit', function (e) {
                            if (!avatarInput.files || avatarInput.files.length === 0) {
                                e.preventDefault();
                                pendingAutoSubmit = true;
                                avatarInput.click();
                            }
                        });
                    }

                    window.QuoteItUI = {
                        toggleTheme: toggleTheme,
                        applyTheme: applyTheme,
                        showToast: showToast,
                        copyQuoteText: copyQuoteText,
                        openModal: openModal,
                        closeModal: closeModal,
                        promptGuestAuth: promptGuestAuth
                    };

                    applyTheme(getCurrentTheme(), false);

                    document.addEventListener('submit', function (e) {
                        var likeForm = e.target.closest('form[data-like-form]');
                        if (likeForm) {
                            handleLikeFormSubmit(likeForm, e);
                            return;
                        }
                        var saveForm = e.target.closest('form[data-save-form]');
                        if (saveForm) {
                            handleSaveFormSubmit(saveForm, e);
                        }
                    });

                    document.addEventListener('click', function (e) {
                        var themeBtn = e.target.closest('[data-theme-toggle]');
                        if (themeBtn) {
                            e.preventDefault();
                            toggleTheme();
                            return;
                        }

                        var likeBtn = e.target.closest('[data-like-button]');
                        if (likeBtn) {
                            var parentLikeForm = likeBtn.closest('form[data-like-form]');
                            if (parentLikeForm) {
                                handleLikeFormSubmit(parentLikeForm, e);
                                return;
                            }
                        }

                        var saveBtn = e.target.closest('[data-save-button]');
                        if (saveBtn) {
                            var parentSaveForm = saveBtn.closest('form[data-save-form]');
                            if (parentSaveForm) {
                                handleSaveFormSubmit(parentSaveForm, e);
                                return;
                            }
                        }

                        var guestPromptTrigger = e.target.closest('[data-require-auth]');
                        if (guestPromptTrigger) {
                            e.preventDefault();
                            e.stopPropagation();
                            promptGuestAuth();
                            return;
                        }

                        var accountMenuBtn = e.target.closest('#account-menu-button');
                        var accountMenuDropdown = document.getElementById('account-menu-dropdown');
                        if (accountMenuBtn && accountMenuDropdown) {
                            e.preventDefault();
                            e.stopPropagation();
                            var isHidden = accountMenuDropdown.classList.contains('hidden');
                            accountMenuDropdown.classList.toggle('hidden', !isHidden);
                            accountMenuBtn.setAttribute('aria-expanded', isHidden ? 'true' : 'false');
                            return;
                        }

                        if (accountMenuDropdown && !accountMenuDropdown.classList.contains('hidden')) {
                            if (!accountMenuDropdown.contains(e.target)) {
                                accountMenuDropdown.classList.add('hidden');
                                var navBtn = document.getElementById('account-menu-button');
                                if (navBtn) navBtn.setAttribute('aria-expanded', 'false');
                            }
                        }

                        var mobileMenuBtn = e.target.closest('#mobile-menu-toggle');
                        var mobileMenuPanel = document.getElementById('mobile-menu-panel');
                        if (mobileMenuBtn && mobileMenuPanel) {
                            e.preventDefault();
                            var expanded = mobileMenuBtn.getAttribute('aria-expanded') === 'true';
                            mobileMenuBtn.setAttribute('aria-expanded', (!expanded).toString());
                            mobileMenuPanel.classList.toggle('hidden', expanded);
                            return;
                        }

                        var avatarTrigger = e.target.closest('[data-avatar-trigger]');
                        if (avatarTrigger) {
                            e.preventDefault();
                            var fileInput = document.getElementById('avatar');
                            if (fileInput) fileInput.click();
                            return;
                        }

                        var openTrigger = e.target.closest('[data-modal-open]');
                        if (openTrigger) {
                            e.preventDefault();
                            openModal(openTrigger.getAttribute('data-modal-open'));
                            return;
                        }

                        var closeTrigger = e.target.closest('[data-modal-close]');
                        if (closeTrigger) {
                            e.preventDefault();
                            closeModal(closeTrigger.getAttribute('data-modal-close'));
                            return;
                        }

                        var copyTrigger = e.target.closest('[data-copy-quote]');
                        if (copyTrigger) {
                            e.preventDefault();
                            var text = copyTrigger.getAttribute('data-copy-quote') || '';
                            var author = copyTrigger.getAttribute('data-copy-author') || '';
                            var formatted = author ? ('"' + text + '" — ' + author) : text;
                            var successMsg = copyTrigger.getAttribute('data-copy-success') || 'Copied';
                            var errorMsg = copyTrigger.getAttribute('data-copy-error') || 'Failed to copy';
                            copyQuoteText(formatted, successMsg, errorMsg);
                        }
                    });

                    document.addEventListener('keydown', function (e) {
                        if (e.key === 'Escape') {
                            document.querySelectorAll('[data-modal-backdrop]:not(.hidden)').forEach(function (modal) {
                                closeModal(modal.id);
                            });
                            var accountMenuDropdown = document.getElementById('account-menu-dropdown');
                            if (accountMenuDropdown && !accountMenuDropdown.classList.contains('hidden')) {
                                accountMenuDropdown.classList.add('hidden');
                                var navBtn = document.getElementById('account-menu-button');
                                if (navBtn) navBtn.setAttribute('aria-expanded', 'false');
                            }
                        }
                    });
                }

                // Initialize Share Quote as PNG Generator
                if (!window.__QUOTEIT_SHARE_BOOTSTRAPPED__) {
                    var shareModal = document.getElementById('share-quote-modal');
                    var shareCanvas = document.getElementById('share-quote-canvas');
                    if (shareModal && shareCanvas) {
                        window.__QUOTEIT_SHARE_BOOTSTRAPPED__ = true;

                        var CANVAS_SIZE = 1080;
                        var TEMPLATES = {
                            editorial: {
                                outerBg: '#F8F4EC',
                                cardBg: '#FFFEFA',
                                secondaryBg: '#EFE5D5',
                                border: '#D2C2AC',
                                accent: '#B99A68',
                                brandColor: '#4A3328',
                                headingText: '#2B1D17',
                                secondaryText: '#827469',
                                badgeBg: '#EFE5D5',
                                badgeText: '#4A3328'
                            },
                            paper: {
                                outerBg: '#FFFEFA',
                                cardBg: '#FFFEFA',
                                secondaryBg: '#F8F4EC',
                                border: '#4A3328',
                                accent: '#B99A68',
                                brandColor: '#2B1D17',
                                headingText: '#2B1D17',
                                secondaryText: '#6E6055',
                                badgeBg: '#F8F4EC',
                                badgeText: '#2B1D17'
                            },
                            dark: {
                                outerBg: '#211A16',
                                cardBg: '#2C231D',
                                secondaryBg: '#362B24',
                                border: '#56453B',
                                accent: '#C9A66B',
                                brandColor: '#C9A66B',
                                headingText: '#F2E8DA',
                                secondaryText: '#C3B4A0',
                                badgeBg: '#362B24',
                                badgeText: '#F2E8DA'
                            },
                            magazine: {
                                outerBg: '#EFE5D5',
                                cardBg: '#FFFEFA',
                                secondaryBg: '#F8F4EC',
                                headerBg: '#4A3328',
                                headerText: '#FFFEFA',
                                border: '#D2C2AC',
                                accent: '#B99A68',
                                brandColor: '#4A3328',
                                headingText: '#2B1D17',
                                secondaryText: '#6E6055',
                                badgeBg: '#B99A68',
                                badgeText: '#2B1D17'
                            }
                        };

                        var ctx = shareCanvas.getContext('2d');
                        var loadingOverlay = document.getElementById('share-canvas-loading');
                        var labels = {
                            brandName: shareModal.getAttribute('data-brand-name') || 'ATHAR',
                            quickQuote: shareModal.getAttribute('data-label-quick') || 'Quick Quote',
                            bookQuote: shareModal.getAttribute('data-label-book') || 'Book Quote',
                            publishedBy: shareModal.getAttribute('data-label-published-by') || 'Published by',
                            attributedTo: shareModal.getAttribute('data-label-attributed-to') || 'Attributed to',
                            fromBook: shareModal.getAttribute('data-label-from-book') || 'From book',
                            msgDownloaded: shareModal.getAttribute('data-msg-downloaded') || 'PNG image downloaded successfully.',
                            msgShared: shareModal.getAttribute('data-msg-shared') || 'Quote image shared successfully.',
                            msgShareUnsupported: shareModal.getAttribute('data-msg-share-unsupported') || 'Direct file sharing is not supported on this browser. Please use Download PNG or Copy Quote Text.',
                            msgCopied: shareModal.getAttribute('data-msg-copied') || 'Copied to clipboard',
                            msgCopyError: shareModal.getAttribute('data-msg-copy-error') || 'Unable to copy quote'
                        };

                        var activeTemplate = 'editorial';
                        var activeQuote = null;
                        var cachedCoverImg = null;
                        var cachedCoverUrl = '';

                        function isArabicScript(str) {
                            return /[\u0600-\u06FF\u0750-\u077F\u08A0-\u08FF\uFB50-\uFDFF\uFE70-\uFEFF]/.test(String(str || ''));
                        }

                        function normalizeUrl(u) {
                            if (!u) return '';
                            try {
                                var p = new URL(u, window.location.origin);
                                return p.pathname + p.search;
                            } catch (e) {
                                return u;
                            }
                        }

                        function drawRoundedRect(c, x, y, w, h, r) {
                            var rad = Math.min(r, w / 2, h / 2);
                            c.beginPath();
                            c.moveTo(x + rad, y);
                            c.lineTo(x + w - rad, y);
                            c.quadraticCurveTo(x + w, y, x + w, y + rad);
                            c.lineTo(x + w, y + h - rad);
                            c.quadraticCurveTo(x + w, y + h, x + w - rad, y + h);
                            c.lineTo(x + rad, y + h);
                            c.quadraticCurveTo(x, y + h, x, y + h - rad);
                            c.lineTo(x, y + rad);
                            c.quadraticCurveTo(x, y, x + rad, y);
                            c.closePath();
                        }

                        function loadCoverImage(url) {
                            return new Promise(function (resolve) {
                                var clean = normalizeUrl(url);
                                if (!clean) {
                                    resolve(null);
                                    return;
                                }
                                var done = false;
                                var timer = setTimeout(function () {
                                    if (!done) {
                                        done = true;
                                        resolve(null);
                                    }
                                }, 2000);
                                var img = new Image();
                                img.crossOrigin = 'anonymous';
                                img.onload = function () {
                                    if (!done) {
                                        done = true;
                                        clearTimeout(timer);
                                        resolve(img);
                                    }
                                };
                                img.onerror = function () {
                                    if (!done) {
                                        done = true;
                                        clearTimeout(timer);
                                        resolve(null);
                                    }
                                };
                                img.src = clean;
                            });
                        }

                        function wrapTextLines(c, text, maxW) {
                            var paragraphs = String(text || '').replace(/\r\n/g, '\n').split('\n');
                            var lines = [];
                            paragraphs.forEach(function (para, idx) {
                                var trimmed = para.trim();
                                if (!trimmed) {
                                    if (idx > 0 && idx < paragraphs.length - 1) lines.push('');
                                    return;
                                }
                                var words = trimmed.split(/\s+/);
                                var current = '';
                                words.forEach(function (word) {
                                    var test = current ? (current + ' ' + word) : word;
                                    if (c.measureText(test).width > maxW && current) {
                                        lines.push(current);
                                        current = word;
                                    } else {
                                        current = test;
                                    }
                                });
                                if (current) lines.push(current);
                            });
                            return lines;
                        }

                        function truncateSingleLine(c, text, maxW) {
                            var raw = String(text || '').trim();
                            if (!raw) return '';
                            if (c.measureText(raw).width <= maxW) return raw;
                            var t = raw;
                            while (t.length > 1 && c.measureText(t + '…').width > maxW) {
                                t = t.slice(0, -1);
                            }
                            return t + '…';
                        }

                        function drawBookFallbackCover(c, x, y, w, h, pal) {
                            c.save();
                            drawRoundedRect(c, x, y, w, h, 14);
                            c.fillStyle = '#4A3328';
                            c.fill();
                            c.lineWidth = 2;
                            c.strokeStyle = pal.accent;
                            c.stroke();
                            c.beginPath();
                            c.moveTo(x + 16, y + 10);
                            c.lineTo(x + 16, y + h - 10);
                            c.strokeStyle = 'rgba(185, 154, 104, 0.55)';
                            c.lineWidth = 2;
                            c.stroke();
                            var cx = x + w / 2 + 4;
                            var cy = y + h / 2;
                            c.strokeStyle = '#B99A68';
                            c.lineWidth = 3;
                            c.strokeRect(cx - 18, cy - 24, 36, 48);
                            c.restore();
                        }

                        function drawImageCoverFit(c, img, x, y, w, h, rad) {
                            c.save();
                            drawRoundedRect(c, x, y, w, h, rad || 14);
                            c.clip();
                            var ir = (img.width || 1) / (img.height || 1);
                            var br = w / h;
                            var dw = w, dh = h, ox = x, oy = y;
                            if (ir > br) {
                                dw = h * ir;
                                ox = x - (dw - w) / 2;
                            } else {
                                dh = w / ir;
                                oy = y - (dh - h) / 2;
                            }
                            c.drawImage(img, ox, oy, dw, dh);
                            c.restore();
                            c.save();
                            drawRoundedRect(c, x, y, w, h, rad || 14);
                            c.lineWidth = 2;
                            c.strokeStyle = 'rgba(185, 154, 104, 0.45)';
                            c.stroke();
                            c.restore();
                        }

                        function updateTemplateButtonsUI() {
                            shareModal.querySelectorAll('[data-share-template]').forEach(function (btn) {
                                var tpl = btn.getAttribute('data-share-template');
                                var selected = tpl === activeTemplate;
                                btn.setAttribute('aria-checked', selected ? 'true' : 'false');
                                if (selected) {
                                    btn.classList.add('border-[#4A3328]', 'dark:border-[#C9A66B]', 'bg-editorial-secondary/70', 'ring-2', 'ring-[#B99A68]/50');
                                    btn.classList.remove('border-editorial', 'bg-editorial-card');
                                } else {
                                    btn.classList.remove('border-[#4A3328]', 'dark:border-[#C9A66B]', 'bg-editorial-secondary/70', 'ring-2', 'ring-[#B99A68]/50');
                                    btn.classList.add('border-editorial', 'bg-editorial-card');
                                }
                            });
                        }

                        async function renderShareCanvas(skipExternalCover) {
                            if (!activeQuote || !ctx) return;
                            if (loadingOverlay) loadingOverlay.classList.remove('hidden');

                            try {
                                if (document.fonts && document.fonts.ready) {
                                    await Promise.race([
                                        document.fonts.ready,
                                        new Promise(function (r) { setTimeout(r, 600); })
                                    ]);
                                }
                            } catch (e) {}

                            var isBook = activeQuote.type === 'book';
                            if (isBook && activeQuote.cover && !skipExternalCover) {
                                if (cachedCoverUrl !== activeQuote.cover) {
                                    cachedCoverImg = await loadCoverImage(activeQuote.cover);
                                    cachedCoverUrl = activeQuote.cover;
                                }
                            } else {
                                cachedCoverImg = null;
                                if (skipExternalCover) cachedCoverUrl = '';
                            }

                            var palette = TEMPLATES[activeTemplate] || TEMPLATES.editorial;
                            var quoteIsArabic = isArabicScript(activeQuote.text);
                            var uiIsArabic = document.documentElement.lang === 'ar' || isArabicScript(labels.publishedBy);
                            var brandIsArabic = isArabicScript(labels.brandName);

                            ctx.clearRect(0, 0, CANVAS_SIZE, CANVAS_SIZE);
                            ctx.fillStyle = palette.outerBg;
                            ctx.fillRect(0, 0, CANVAS_SIZE, CANVAS_SIZE);

                            var cardX = 64, cardY = 64, cardW = CANVAS_SIZE - 128, cardH = CANVAS_SIZE - 128;
                            ctx.save();
                            drawRoundedRect(ctx, cardX, cardY, cardW, cardH, 32);
                            ctx.fillStyle = palette.cardBg;
                            ctx.fill();
                            ctx.lineWidth = activeTemplate === 'paper' ? 3 : 2;
                            ctx.strokeStyle = palette.border;
                            ctx.stroke();
                            ctx.restore();

                            if (activeTemplate === 'editorial' || activeTemplate === 'dark') {
                                ctx.save();
                                drawRoundedRect(ctx, cardX + 18, cardY + 18, cardW - 36, cardH - 36, 22);
                                ctx.lineWidth = 1.5;
                                ctx.strokeStyle = palette.accent;
                                ctx.globalAlpha = 0.45;
                                ctx.stroke();
                                ctx.restore();
                            } else if (activeTemplate === 'paper') {
                                ctx.save();
                                drawRoundedRect(ctx, cardX + 14, cardY + 14, cardW - 28, cardH - 28, 24);
                                ctx.lineWidth = 1;
                                ctx.strokeStyle = palette.border;
                                ctx.globalAlpha = 0.6;
                                ctx.stroke();
                                ctx.restore();
                            } else if (activeTemplate === 'magazine') {
                                ctx.save();
                                drawRoundedRect(ctx, cardX, cardY, cardW, 136, 32);
                                ctx.fillStyle = palette.headerBg;
                                ctx.fill();
                                ctx.fillRect(cardX, cardY + 100, cardW, 36);
                                ctx.fillStyle = palette.accent;
                                ctx.fillRect(cardX, cardY + 132, cardW, 6);
                                ctx.restore();
                            }

                            var leftX = 124, rightX = CANVAS_SIZE - 124;
                            var headerTextY = activeTemplate === 'magazine' ? cardY + 78 : cardY + 86;
                            var brandTitleColor = activeTemplate === 'magazine' ? palette.headerText : palette.brandColor;

                            ctx.save();
                            ctx.direction = brandIsArabic ? 'rtl' : 'ltr';
                            ctx.textAlign = 'left';
                            ctx.fillStyle = brandTitleColor;
                            ctx.font = brandIsArabic ? "700 36px 'Tajawal', sans-serif" : "700 34px 'Lora', 'Tajawal', Georgia, serif";
                            ctx.fillText(labels.brandName, leftX, headerTextY);

                            var badgeLabel = isBook ? labels.bookQuote : labels.quickQuote;
                            ctx.font = "700 22px 'Tajawal', 'Inter', sans-serif";
                            var badgeW = ctx.measureText(badgeLabel).width + 44;
                            var badgeH = 46;
                            var badgeX = rightX - badgeW;
                            var badgeY = headerTextY - 32;

                            drawRoundedRect(ctx, badgeX, badgeY, badgeW, badgeH, 14);
                            ctx.fillStyle = palette.badgeBg;
                            ctx.fill();
                            ctx.lineWidth = 1.5;
                            ctx.strokeStyle = palette.accent;
                            ctx.stroke();

                            ctx.direction = isArabicScript(badgeLabel) ? 'rtl' : 'ltr';
                            ctx.textAlign = 'center';
                            ctx.fillStyle = palette.badgeText;
                            ctx.fillText(badgeLabel, badgeX + badgeW / 2, badgeY + 31);
                            ctx.restore();

                            if (activeTemplate !== 'magazine') {
                                ctx.save();
                                ctx.beginPath();
                                ctx.moveTo(leftX, cardY + 124);
                                ctx.lineTo(rightX, cardY + 124);
                                ctx.strokeStyle = palette.border;
                                ctx.lineWidth = 1.5;
                                ctx.stroke();
                                ctx.restore();
                            }

                            ctx.save();
                            ctx.font = '700 150px Georgia, serif';
                            ctx.fillStyle = palette.accent;
                            ctx.globalAlpha = activeTemplate === 'dark' ? 0.16 : 0.18;
                            ctx.textAlign = quoteIsArabic ? 'right' : 'left';
                            ctx.fillText('“', quoteIsArabic ? rightX : leftX, cardY + 255);
                            ctx.restore();

                            var quoteTopY = cardY + 185;
                            var quoteBottomY = isBook ? (CANVAS_SIZE - 365) : (CANVAS_SIZE - 210);
                            var availableQuoteHeight = quoteBottomY - quoteTopY;
                            var maxQuoteWidth = rightX - leftX;
                            var quoteFontFamily = quoteIsArabic ? "'Tajawal', sans-serif" : "'Lora', 'Tajawal', Georgia, serif";

                            var candidateSizes = [46, 42, 38, 34, 30, 26, 23];
                            var chosenSize = 26;
                            var wrappedLines = [];
                            var lineHeight = 40;
                            var openMark = quoteIsArabic ? '«' : '“';
                            var closeMark = quoteIsArabic ? '»' : '”';
                            var decoratedQuoteText = openMark + activeQuote.text + closeMark;

                            for (var i = 0; i < candidateSizes.length; i++) {
                                var sz = candidateSizes[i];
                                ctx.font = '600 ' + sz + 'px ' + quoteFontFamily;
                                var testLines = wrapTextLines(ctx, decoratedQuoteText, maxQuoteWidth);
                                var testLh = Math.round(sz * (quoteIsArabic ? 1.58 : 1.48));
                                if (testLines.length * testLh <= availableQuoteHeight) {
                                    chosenSize = sz;
                                    wrappedLines = testLines;
                                    lineHeight = testLh;
                                    break;
                                }
                            }

                            if (wrappedLines.length === 0) {
                                chosenSize = 23;
                                lineHeight = 35;
                                ctx.font = '600 23px ' + quoteFontFamily;
                                wrappedLines = wrapTextLines(ctx, decoratedQuoteText, maxQuoteWidth);
                                var maxLines = Math.max(1, Math.floor(availableQuoteHeight / lineHeight));
                                if (wrappedLines.length > maxLines) {
                                    wrappedLines = wrappedLines.slice(0, maxLines);
                                    wrappedLines[maxLines - 1] = truncateSingleLine(ctx, wrappedLines[maxLines - 1], maxQuoteWidth - 30);
                                }
                            }

                            var hasQuickAuthor = !isBook && activeQuote.author && String(activeQuote.author).trim() !== '';
                            var quickAuthorOffset = hasQuickAuthor ? 48 : 0;
                            var totalTextHeight = wrappedLines.length * lineHeight + quickAuthorOffset;
                            var currentLineY = quoteTopY + Math.max(chosenSize, (availableQuoteHeight - totalTextHeight) / 2 + chosenSize * 0.85);

                            ctx.save();
                            ctx.font = '600 ' + chosenSize + 'px ' + quoteFontFamily;
                            ctx.fillStyle = palette.headingText;
                            var centerAlign = activeTemplate === 'paper';
                            ctx.direction = quoteIsArabic ? 'rtl' : 'ltr';
                            ctx.textAlign = centerAlign ? 'center' : (quoteIsArabic ? 'right' : 'left');
                            var textAnchorX = centerAlign ? (CANVAS_SIZE / 2) : (quoteIsArabic ? rightX : leftX);

                            wrappedLines.forEach(function (line) {
                                if (line !== '') ctx.fillText(line, textAnchorX, currentLineY);
                                currentLineY += lineHeight;
                            });

                            if (hasQuickAuthor) {
                                var authorIsAr = isArabicScript(activeQuote.author);
                                ctx.direction = authorIsAr ? 'rtl' : (quoteIsArabic ? 'rtl' : 'ltr');
                                ctx.font = "500 26px 'Tajawal', 'Inter', sans-serif";
                                ctx.fillStyle = palette.secondaryText;
                                ctx.fillText('— ' + String(activeQuote.author).trim(), textAnchorX, currentLineY + 12);
                            }
                            ctx.restore();

                            if (isBook) {
                                var bookBoxY = CANVAS_SIZE - 340;
                                var bookBoxH = 148;
                                var bookBoxW = rightX - leftX;

                                ctx.save();
                                drawRoundedRect(ctx, leftX, bookBoxY, bookBoxW, bookBoxH, 20);
                                ctx.fillStyle = palette.secondaryBg;
                                ctx.fill();
                                ctx.lineWidth = 1.5;
                                ctx.strokeStyle = palette.border;
                                ctx.stroke();
                                ctx.restore();

                                var coverW = 84, coverH = 116;
                                var coverY = bookBoxY + (bookBoxH - coverH) / 2;
                                var coverX = uiIsArabic ? (rightX - 20 - coverW) : (leftX + 20);

                                if (cachedCoverImg) {
                                    drawImageCoverFit(ctx, cachedCoverImg, coverX, coverY, coverW, coverH, 12);
                                } else {
                                    drawBookFallbackCover(ctx, coverX, coverY, coverW, coverH, palette);
                                }

                                var metaMaxW = bookBoxW - coverW - 68;
                                var metaAnchorX = uiIsArabic ? (coverX - 24) : (coverX + coverW + 24);

                                ctx.save();
                                ctx.direction = uiIsArabic ? 'rtl' : 'ltr';
                                ctx.textAlign = uiIsArabic ? 'right' : 'left';
                                ctx.font = "700 25px 'Tajawal', 'Inter', sans-serif";
                                ctx.fillStyle = palette.headingText;
                                ctx.fillText(truncateSingleLine(ctx, labels.fromBook + ': ' + (activeQuote.book || ''), metaMaxW), metaAnchorX, bookBoxY + 62);

                                ctx.font = "500 23px 'Tajawal', 'Inter', sans-serif";
                                ctx.fillStyle = palette.secondaryText;
                                ctx.fillText(truncateSingleLine(ctx, labels.attributedTo + ': ' + (activeQuote.author || ''), metaMaxW), metaAnchorX, bookBoxY + 104);
                                ctx.restore();
                            }

                            var footerDividerY = CANVAS_SIZE - 165;
                            var footerTextY = CANVAS_SIZE - 115;

                            ctx.save();
                            ctx.beginPath();
                            ctx.moveTo(leftX, footerDividerY);
                            ctx.lineTo(rightX, footerDividerY);
                            ctx.strokeStyle = palette.border;
                            ctx.lineWidth = 1.5;
                            ctx.stroke();

                            var publisherText = labels.publishedBy + ': ' + (activeQuote.publisher || '');
                            ctx.font = "600 23px 'Tajawal', 'Inter', sans-serif";
                            ctx.fillStyle = palette.headingText;
                            ctx.direction = uiIsArabic ? 'rtl' : 'ltr';
                            ctx.textAlign = uiIsArabic ? 'right' : 'left';
                            ctx.fillText(truncateSingleLine(ctx, publisherText, (rightX - leftX) * 0.62), uiIsArabic ? rightX : leftX, footerTextY);

                            ctx.font = brandIsArabic ? "700 24px 'Tajawal', sans-serif" : "600 21px 'Lora', 'Tajawal', sans-serif";
                            ctx.fillStyle = palette.accent;
                            ctx.direction = brandIsArabic ? 'rtl' : 'ltr';
                            ctx.textAlign = uiIsArabic ? 'left' : 'right';
                            ctx.fillText(labels.brandName, uiIsArabic ? leftX : rightX, footerTextY);
                            ctx.restore();

                            if (loadingOverlay) loadingOverlay.classList.add('hidden');
                        }

                        function extractBlob() {
                            return new Promise(function (resolve, reject) {
                                try {
                                    if (shareCanvas.toBlob) {
                                        shareCanvas.toBlob(function (b) {
                                            if (b) resolve(b);
                                            else reject(new Error('Empty blob'));
                                        }, 'image/png', 1.0);
                                    } else {
                                        var dataUrl = shareCanvas.toDataURL('image/png');
                                        var parts = dataUrl.split(',');
                                        var bs = atob(parts[1]);
                                        var mime = parts[0].split(':')[1].split(';')[0];
                                        var ab = new ArrayBuffer(bs.length);
                                        var ia = new Uint8Array(ab);
                                        for (var i = 0; i < bs.length; i++) ia[i] = bs.charCodeAt(i);
                                        resolve(new Blob([ab], { type: mime }));
                                    }
                                } catch (err) {
                                    reject(err);
                                }
                            });
                        }

                        async function getSafeBlob() {
                            await renderShareCanvas(false);
                            try {
                                return await extractBlob();
                            } catch (err) {
                                await renderShareCanvas(true);
                                return await extractBlob();
                            }
                        }

                        async function downloadPng() {
                            if (!activeQuote) return;
                            try {
                                var blob = await getSafeBlob();
                                if (!blob) return;
                                var filename = 'quoteit-quote-' + (activeQuote.id || 'card') + '-' + activeTemplate + '.png';
                                var url = URL.createObjectURL(blob);
                                var a = document.createElement('a');
                                a.href = url;
                                a.download = filename;
                                document.body.appendChild(a);
                                a.click();
                                document.body.removeChild(a);
                                setTimeout(function () { URL.revokeObjectURL(url); }, 3000);
                                window.QuoteItUI.showToast(labels.msgDownloaded, 'success');
                            } catch (e) {
                                window.QuoteItUI.showToast(labels.msgCopyError, 'error');
                            }
                        }

                        async function shareNativePng() {
                            if (!activeQuote) return;
                            try {
                                var blob = await getSafeBlob();
                                if (!blob) return;
                                var filename = 'quoteit-quote-' + (activeQuote.id || 'card') + '-' + activeTemplate + '.png';
                                var file = new File([blob], filename, { type: 'image/png' });
                                if (navigator.canShare && navigator.canShare({ files: [file] })) {
                                    try {
                                        await navigator.share({
                                            title: labels.brandName,
                                            text: '"' + activeQuote.text + '"',
                                            files: [file]
                                        });
                                        window.QuoteItUI.showToast(labels.msgShared, 'success');
                                        return;
                                    } catch (err) {
                                        if (err && err.name === 'AbortError') return;
                                    }
                                }
                                window.QuoteItUI.showToast(labels.msgShareUnsupported, 'error');
                            } catch (e) {
                                window.QuoteItUI.showToast(labels.msgShareUnsupported, 'error');
                            }
                        }

                        function copyActiveQuoteText() {
                            if (!activeQuote) return;
                            var attribution = activeQuote.type === 'book'
                                ? (activeQuote.author + (activeQuote.book ? (' (' + activeQuote.book + ')') : ''))
                                : (activeQuote.author || activeQuote.publisher);
                            var formatted = attribution ? ('"' + activeQuote.text + '" — ' + attribution) : activeQuote.text;
                            window.QuoteItUI.copyQuoteText(formatted, labels.msgCopied, labels.msgCopyError);
                        }

                        document.addEventListener('click', function (e) {
                            var shareTrigger = e.target.closest('[data-share-quote]');
                            if (shareTrigger) {
                                e.preventDefault();
                                e.stopPropagation();
                                var qId = shareTrigger.getAttribute('data-share-quote');
                                var card = shareTrigger.closest('[data-quote-card]') || document.getElementById('quote-card-' + qId);
                                if (!card) return;

                                activeQuote = {
                                    id: card.getAttribute('data-quote-id') || qId,
                                    type: card.getAttribute('data-quote-type') || 'quick',
                                    text: card.getAttribute('data-quote-text') || '',
                                    publisher: card.getAttribute('data-quote-publisher') || '',
                                    author: card.getAttribute('data-quote-author') || '',
                                    book: card.getAttribute('data-quote-book') || '',
                                    cover: card.getAttribute('data-quote-cover') || ''
                                };

                                updateTemplateButtonsUI();
                                window.QuoteItUI.openModal('share-quote-modal');
                                renderShareCanvas(false);
                                return;
                            }

                            var tplBtn = e.target.closest('[data-share-template]');
                            if (tplBtn && shareModal.contains(tplBtn)) {
                                e.preventDefault();
                                activeTemplate = tplBtn.getAttribute('data-share-template') || 'editorial';
                                updateTemplateButtonsUI();
                                renderShareCanvas(false);
                                return;
                            }

                            if (e.target.closest('#share-download-png-btn')) {
                                e.preventDefault();
                                downloadPng();
                                return;
                            }

                            if (e.target.closest('#share-native-file-btn')) {
                                e.preventDefault();
                                shareNativePng();
                                return;
                            }

                            if (e.target.closest('#share-copy-text-btn')) {
                                e.preventDefault();
                                copyActiveQuoteText();
                            }
                        });
                    }
                }
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', bootQuoteItFallback);
            } else {
                bootQuoteItFallback();
            }
        })();
    </script>
</body>
</html>