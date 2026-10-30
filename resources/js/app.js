/**
 * QuoteIt — Shared Application Shell & Editorial UI Interactions
 * Handles:
 *  - Persistent Light/Dark theme switching (with Sun/Moon icon state & aria-label updates)
 *  - Mobile navigation drawer & Account Avatar dropdown menu
 *  - Account page Avatar file selection, live preview & auto-submit
 *  - Accessible Modal dialogs & Guest Sign-In prompt modal
 *  - Clipboard copy action with localized toast feedback
 *  - Scroll-preserving AJAX Like/Unlike and Save/Unsave handlers
 *  - High-Resolution PNG Share Generator Modal & Multi-Template Canvas Renderer
 */

import { initShareGenerator } from './share-generator';

const STORAGE_THEME_KEY = 'quoteit_theme';

function getCurrentTheme() {
    return document.documentElement.classList.contains('dark') ? 'dark' : 'light';
}

function applyTheme(theme, persist = true) {
    const root = document.documentElement;
    const isDark = theme === 'dark';

    root.classList.toggle('dark', isDark);
    root.setAttribute('data-theme', isDark ? 'dark' : 'light');
    root.style.colorScheme = isDark ? 'dark' : 'light';

    if (persist) {
        try {
            localStorage.setItem(STORAGE_THEME_KEY, isDark ? 'dark' : 'light');
        } catch (e) {
            // Ignore localStorage restrictions in private browsing
        }
    }

    document.querySelectorAll('[data-theme-toggle]').forEach((btn) => {
        const lightLabel = btn.getAttribute('data-label-light') || 'Light mode active — switch to dark mode';
        const darkLabel = btn.getAttribute('data-label-dark') || 'Dark mode active — switch to light mode';
        const label = isDark ? darkLabel : lightLabel;

        btn.setAttribute('aria-label', label);
        btn.setAttribute('title', label);
        btn.setAttribute('aria-pressed', isDark ? 'true' : 'false');

        const sunIcon = btn.querySelector('[data-theme-icon="sun"]');
        const moonIcon = btn.querySelector('[data-theme-icon="moon"]');

        if (sunIcon && moonIcon) {
            sunIcon.classList.toggle('hidden', isDark);
            moonIcon.classList.toggle('hidden', !isDark);
        }
    });
}

function toggleTheme() {
    const nextTheme = getCurrentTheme() === 'dark' ? 'light' : 'dark';
    applyTheme(nextTheme, true);
}

function showToast(message, type = 'success') {
    const container = document.getElementById('quoteit-toast-container');
    if (!container || !message) return;

    const toast = document.createElement('div');
    const isError = type === 'error';

    toast.className = [
        'pointer-events-auto flex items-center gap-3 px-4 py-3 rounded-xl border shadow-lg text-sm font-medium transition-all duration-200',
        isError
            ? 'bg-red-950/90 text-red-100 border-red-700'
            : 'bg-[#2B1D17] text-[#F8F4EC] border-[#B99A68]',
    ].join(' ');
    toast.setAttribute('role', 'status');
    toast.setAttribute('aria-live', 'polite');

    const dot = document.createElement('span');
    dot.className = `w-2 h-2 rounded-full shrink-0 ${isError ? 'bg-red-400' : 'bg-[#B99A68]'}`;

    const textSpan = document.createElement('span');
    textSpan.textContent = message;

    toast.appendChild(dot);
    toast.appendChild(textSpan);
    container.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(6px)';
        setTimeout(() => toast.remove(), 220);
    }, 3200);
}

async function copyQuoteText(text, successMsg, errorMsg) {
    if (!text) return false;

    try {
        if (navigator.clipboard && window.isSecureContext) {
            await navigator.clipboard.writeText(text);
            showToast(successMsg, 'success');
            return true;
        }
    } catch (err) {
        // Fall through to textarea fallback
    }

    try {
        const textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.setAttribute('readonly', '');
        textarea.style.position = 'fixed';
        textarea.style.top = '0';
        textarea.style.opacity = '0';
        textarea.style.pointerEvents = 'none';
        document.body.appendChild(textarea);
        textarea.select();
        const copied = document.execCommand('copy');
        document.body.removeChild(textarea);

        if (copied) {
            showToast(successMsg, 'success');
            return true;
        }
    } catch (fallbackErr) {
        // Fall through to error toast
    }

    showToast(errorMsg, 'error');
    return false;
}

function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (!modal) return;
    modal.classList.remove('hidden');
    modal.setAttribute('aria-hidden', 'false');
    document.body.classList.add('overflow-hidden');

    const focusable = modal.querySelector('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
    if (focusable) {
        setTimeout(() => focusable.focus({ preventScroll: true }), 30);
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (!modal) return;
    modal.classList.add('hidden');
    modal.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('overflow-hidden');
}

function promptGuestAuth() {
    openModal('guest-auth-modal');
}

function getCsrfToken(form) {
    const formToken = form?.querySelector('input[name="_token"]')?.value;
    if (formToken) return formToken;
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
}

function resolveSameOriginAction(actionUrl) {
    try {
        const parsed = new URL(actionUrl, window.location.origin);
        return parsed.pathname + parsed.search;
    } catch (e) {
        return actionUrl;
    }
}

async function handleLikeFormSubmit(form, e) {
    if (e) {
        e.preventDefault();
        e.stopPropagation();
    }

    if (!form || form.dataset.submitting === 'true') return;

    const quoteId = form.getAttribute('data-like-form');
    const button = form.querySelector('[data-like-button]');
    form.dataset.submitting = 'true';

    if (button) {
        button.setAttribute('aria-busy', 'true');
        button.classList.add('opacity-75', 'pointer-events-none');
    }

    try {
        const formData = new FormData(form);
        const csrfToken = getCsrfToken(form);
        if (csrfToken && !formData.has('_token')) {
            formData.append('_token', csrfToken);
        }

        const response = await fetch(resolveSameOriginAction(form.action), {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: formData,
        });

        if (response.status === 401 || response.status === 419) {
            promptGuestAuth();
            return;
        }

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        const data = await response.json();
        const isLiked = Boolean(data.liked);

        document.querySelectorAll(`[data-like-button="${quoteId}"]`).forEach((btn) => {
            btn.setAttribute('aria-pressed', isLiked ? 'true' : 'false');
            btn.className = [
                'px-3 py-1.5 rounded-xl text-xs font-semibold inline-flex items-center gap-1.5 transition-all cursor-pointer',
                isLiked ? 'theme-brand-pill' : 'btn-editorial-secondary',
            ].join(' ');
        });

        document.querySelectorAll(`[data-like-icon="${quoteId}"]`).forEach((icon) => {
            icon.classList.toggle('fill-current', isLiked);
            icon.classList.toggle('fill-none', !isLiked);
        });

        document.querySelectorAll(`[data-like-count="${quoteId}"]`).forEach((countEl) => {
            countEl.textContent = String(data.likes_count ?? 0);
        });

        if (typeof data.received_likes_count === 'number') {
            document.querySelectorAll('[data-navbar-likes-received], [data-stat-received-likes]').forEach((el) => {
                el.textContent = String(data.received_likes_count);
            });
        }

        if (data.message) {
            showToast(data.message, 'success');
        }
    } catch (error) {
        showToast(
            document.documentElement.lang === 'ar'
                ? 'تعذر تحديث الإعجاب حالياً. يرجى المحاولة مرة أخرى.'
                : 'Unable to update like right now. Please try again.',
            'error'
        );
    } finally {
        form.dataset.submitting = 'false';
        if (button) {
            button.removeAttribute('aria-busy');
            button.classList.remove('opacity-75', 'pointer-events-none');
        }
    }
}

async function handleSaveFormSubmit(form, e) {
    if (e) {
        e.preventDefault();
        e.stopPropagation();
    }

    if (!form || form.dataset.submitting === 'true') return;

    const quoteId = form.getAttribute('data-save-form');
    const button = form.querySelector('[data-save-button]');
    form.dataset.submitting = 'true';

    if (button) {
        button.setAttribute('aria-busy', 'true');
        button.classList.add('opacity-75', 'pointer-events-none');
    }

    try {
        const formData = new FormData(form);
        const csrfToken = getCsrfToken(form);
        if (csrfToken && !formData.has('_token')) {
            formData.append('_token', csrfToken);
        }

        const response = await fetch(resolveSameOriginAction(form.action), {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: formData,
        });

        if (response.status === 401 || response.status === 419) {
            promptGuestAuth();
            return;
        }

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        const data = await response.json();
        const isSaved = Boolean(data.saved);

        document.querySelectorAll(`[data-save-button="${quoteId}"]`).forEach((btn) => {
            btn.setAttribute('aria-pressed', isSaved ? 'true' : 'false');
            btn.className = [
                'px-3 py-1.5 rounded-xl text-xs font-semibold inline-flex items-center gap-1.5 transition-all cursor-pointer',
                isSaved ? 'bg-[#B99A68] text-[#2B1D17]' : 'btn-editorial-secondary',
            ].join(' ');
        });

        document.querySelectorAll(`[data-save-icon="${quoteId}"]`).forEach((icon) => {
            icon.classList.toggle('fill-current', isSaved);
            icon.classList.toggle('fill-none', !isSaved);
        });

        document.querySelectorAll(`[data-save-label="${quoteId}"]`).forEach((labelEl) => {
            labelEl.textContent = data.label || (isSaved ? 'Saved' : 'Save');
        });

        const savedPageGrid = document.querySelector('[data-saved-page-grid]');
        if (savedPageGrid && !isSaved) {
            const card = document.getElementById(`quote-card-${quoteId}`);
            if (card) {
                card.style.opacity = '0';
                card.style.transform = 'scale(0.96)';
                setTimeout(() => {
                    card.remove();
                    if (savedPageGrid.querySelectorAll('[data-quote-card]').length === 0) {
                        window.location.reload();
                    }
                }, 180);
            }
        }

        if (data.message) {
            showToast(data.message, 'success');
        }
    } catch (error) {
        showToast(
            document.documentElement.lang === 'ar'
                ? 'تعذر تحديث الحفظ حالياً. يرجى المحاولة مرة أخرى.'
                : 'Unable to update saved state right now. Please try again.',
            'error'
        );
    } finally {
        form.dataset.submitting = 'false';
        if (button) {
            button.removeAttribute('aria-busy');
            button.classList.remove('opacity-75', 'pointer-events-none');
        }
    }
}

function initAvatarControls() {
    const avatarInput = document.getElementById('avatar');
    const avatarForm = document.getElementById('account-avatar-form');
    const previewImg = document.getElementById('account-avatar-preview-img');
    const filenameLabel = document.getElementById('account-avatar-filename');
    let pendingAutoSubmit = false;

    if (!avatarInput) return;

    avatarInput.addEventListener('change', (e) => {
        const file = e.target.files && e.target.files[0];
        if (!file) return;

        if (filenameLabel) {
            filenameLabel.textContent = file.name;
            filenameLabel.classList.remove('hidden');
        }

        if (previewImg) {
            const reader = new FileReader();
            reader.onload = (ev) => {
                previewImg.src = ev.target.result;
                previewImg.classList.remove('hidden');
                const fallbackSpan = previewImg.parentElement?.querySelector('[data-avatar-initials]');
                if (fallbackSpan) {
                    fallbackSpan.classList.add('hidden');
                }
            };
            reader.readAsDataURL(file);
        }

        if (pendingAutoSubmit && avatarForm) {
            pendingAutoSubmit = false;
            avatarForm.submit();
        }
    });

    if (avatarForm) {
        avatarForm.addEventListener('submit', (e) => {
            if (!avatarInput.files || avatarInput.files.length === 0) {
                e.preventDefault();
                pendingAutoSubmit = true;
                avatarInput.click();
            }
        });
    }
}

window.QuoteItUI = {
    toggleTheme,
    applyTheme,
    showToast,
    copyQuoteText,
    openModal,
    closeModal,
    promptGuestAuth,
};

function initQuoteItApp() {
    // Always initialize the PNG Share Generator
    try {
        initShareGenerator();
    } catch (err) {
        console.error('Share generator init warning:', err);
    }

    if (window.__QUOTEIT_BOOTSTRAPPED__) {
        return;
    }
    window.__QUOTEIT_BOOTSTRAPPED__ = true;

    applyTheme(getCurrentTheme(), false);
    initAvatarControls();

    document.addEventListener('submit', (e) => {
        const likeForm = e.target.closest('form[data-like-form]');
        if (likeForm) {
            handleLikeFormSubmit(likeForm, e);
            return;
        }

        const saveForm = e.target.closest('form[data-save-form]');
        if (saveForm) {
            handleSaveFormSubmit(saveForm, e);
        }
    });

    document.addEventListener('click', (e) => {
        const themeBtn = e.target.closest('[data-theme-toggle]');
        if (themeBtn) {
            e.preventDefault();
            toggleTheme();
            return;
        }

        const likeBtn = e.target.closest('[data-like-button]');
        if (likeBtn) {
            const parentLikeForm = likeBtn.closest('form[data-like-form]');
            if (parentLikeForm) {
                handleLikeFormSubmit(parentLikeForm, e);
                return;
            }
        }

        const saveBtn = e.target.closest('[data-save-button]');
        if (saveBtn) {
            const parentSaveForm = saveBtn.closest('form[data-save-form]');
            if (parentSaveForm) {
                handleSaveFormSubmit(parentSaveForm, e);
                return;
            }
        }

        const guestPromptTrigger = e.target.closest('[data-require-auth]');
        if (guestPromptTrigger) {
            e.preventDefault();
            e.stopPropagation();
            promptGuestAuth();
            return;
        }

        const accountMenuBtn = e.target.closest('#account-menu-button');
        const accountMenuDropdown = document.getElementById('account-menu-dropdown');
        if (accountMenuBtn && accountMenuDropdown) {
            e.preventDefault();
            e.stopPropagation();
            const isHidden = accountMenuDropdown.classList.contains('hidden');
            accountMenuDropdown.classList.toggle('hidden', !isHidden);
            accountMenuBtn.setAttribute('aria-expanded', isHidden ? 'true' : 'false');
            return;
        }

        if (accountMenuDropdown && !accountMenuDropdown.classList.contains('hidden')) {
            if (!accountMenuDropdown.contains(e.target)) {
                accountMenuDropdown.classList.add('hidden');
                document.getElementById('account-menu-button')?.setAttribute('aria-expanded', 'false');
            }
        }

        const mobileMenuBtn = e.target.closest('#mobile-menu-toggle');
        const mobileMenuPanel = document.getElementById('mobile-menu-panel');
        if (mobileMenuBtn && mobileMenuPanel) {
            e.preventDefault();
            const expanded = mobileMenuBtn.getAttribute('aria-expanded') === 'true';
            mobileMenuBtn.setAttribute('aria-expanded', (!expanded).toString());
            mobileMenuPanel.classList.toggle('hidden', expanded);
            return;
        }

        const avatarTrigger = e.target.closest('[data-avatar-trigger]');
        if (avatarTrigger) {
            e.preventDefault();
            const fileInput = document.getElementById('avatar');
            if (fileInput) fileInput.click();
            return;
        }

        const openTrigger = e.target.closest('[data-modal-open]');
        if (openTrigger) {
            e.preventDefault();
            openModal(openTrigger.getAttribute('data-modal-open'));
            return;
        }

        const closeTrigger = e.target.closest('[data-modal-close]');
        if (closeTrigger) {
            e.preventDefault();
            closeModal(closeTrigger.getAttribute('data-modal-close'));
            return;
        }

        const copyTrigger = e.target.closest('[data-copy-quote]');
        if (copyTrigger) {
            e.preventDefault();
            const text = copyTrigger.getAttribute('data-copy-quote') || '';
            const author = copyTrigger.getAttribute('data-copy-author') || '';
            const formatted = author ? `"${text}" — ${author}` : text;
            const successMsg = copyTrigger.getAttribute('data-copy-success') || 'Copied';
            const errorMsg = copyTrigger.getAttribute('data-copy-error') || 'Failed to copy';
            copyQuoteText(formatted, successMsg, errorMsg);
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            document.querySelectorAll('[data-modal-backdrop]:not(.hidden)').forEach((modal) => {
                closeModal(modal.id);
            });
            const accountMenuDropdown = document.getElementById('account-menu-dropdown');
            if (accountMenuDropdown && !accountMenuDropdown.classList.contains('hidden')) {
                accountMenuDropdown.classList.add('hidden');
                document.getElementById('account-menu-button')?.setAttribute('aria-expanded', 'false');
            }
        }
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initQuoteItApp);
} else {
    initQuoteItApp();
}