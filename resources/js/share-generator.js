/**
 * QuoteIt — High-Resolution PNG Share Card Generator
 *
 * Supports:
 *  - 4 selectable templates: 'editorial', 'paper', 'dark', 'magazine'
 *  - Quick Quotes ('quick') and Book Quotes ('book')
 *  - Arabic RTL and English LTR multiline text wrapping & dynamic font sizing
 *  - High-DPI 1080x1080 PNG download, Web Share API file sharing, and Copy Quote Text fallback
 */

const CANVAS_SIZE = 1080;

const TEMPLATES = {
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
        badgeText: '#4A3328',
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
        badgeText: '#2B1D17',
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
        badgeText: '#F2E8DA',
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
        badgeText: '#2B1D17',
    },
};

function isArabicScript(text) {
    return /[\u0600-\u06FF\u0750-\u077F\u08A0-\u08FF\uFB50-\uFDFF\uFE70-\uFEFF]/.test(String(text || ''));
}

function normalizeSameOriginUrl(url) {
    if (!url) return '';
    try {
        const parsed = new URL(url, window.location.origin);
        return parsed.pathname + parsed.search;
    } catch (e) {
        return url;
    }
}

function drawRoundedRect(ctx, x, y, width, height, radius) {
    const r = Math.min(radius, width / 2, height / 2);
    ctx.beginPath();
    ctx.moveTo(x + r, y);
    ctx.lineTo(x + width - r, y);
    ctx.quadraticCurveTo(x + width, y, x + width, y + r);
    ctx.lineTo(x + width, y + height - r);
    ctx.quadraticCurveTo(x + width, y + height, x + width - r, y + height);
    ctx.lineTo(x + r, y + height);
    ctx.quadraticCurveTo(x, y + height, x, y + height - r);
    ctx.lineTo(x, y + r);
    ctx.quadraticCurveTo(x, y, x + r, y);
    ctx.closePath();
}

function loadCoverImage(url) {
    return new Promise((resolve) => {
        const cleanUrl = normalizeSameOriginUrl(url);
        if (!cleanUrl) {
            resolve(null);
            return;
        }

        let settled = false;
        const timer = setTimeout(() => {
            if (!settled) {
                settled = true;
                resolve(null);
            }
        }, 2000);

        const img = new Image();
        img.crossOrigin = 'anonymous';
        img.onload = () => {
            if (!settled) {
                settled = true;
                clearTimeout(timer);
                resolve(img);
            }
        };
        img.onerror = () => {
            if (!settled) {
                settled = true;
                clearTimeout(timer);
                resolve(null);
            }
        };
        img.src = cleanUrl;
    });
}

function wrapTextLines(ctx, text, maxWidth) {
    const paragraphs = String(text || '').replace(/\r\n/g, '\n').split('\n');
    const lines = [];

    paragraphs.forEach((paragraph, pIndex) => {
        const trimmed = paragraph.trim();
        if (!trimmed) {
            if (pIndex > 0 && pIndex < paragraphs.length - 1) {
                lines.push('');
            }
            return;
        }

        const words = trimmed.split(/\s+/);
        let currentLine = '';

        words.forEach((word) => {
            const testLine = currentLine ? `${currentLine} ${word}` : word;
            const metrics = ctx.measureText(testLine);

            if (metrics.width > maxWidth && currentLine) {
                lines.push(currentLine);
                currentLine = word;
            } else {
                currentLine = testLine;
            }
        });

        if (currentLine) {
            lines.push(currentLine);
        }
    });

    return lines;
}

function truncateSingleLine(ctx, text, maxWidth) {
    const raw = String(text || '').trim();
    if (!raw) return '';
    if (ctx.measureText(raw).width <= maxWidth) return raw;

    let truncated = raw;
    while (truncated.length > 1 && ctx.measureText(truncated + '…').width > maxWidth) {
        truncated = truncated.slice(0, -1);
    }
    return truncated + '…';
}

function drawBookFallbackCover(ctx, x, y, w, h, palette) {
    ctx.save();
    drawRoundedRect(ctx, x, y, w, h, 14);
    ctx.fillStyle = '#4A3328';
    ctx.fill();
    ctx.lineWidth = 2;
    ctx.strokeStyle = palette.accent;
    ctx.stroke();

    ctx.beginPath();
    ctx.moveTo(x + 16, y + 10);
    ctx.lineTo(x + 16, y + h - 10);
    ctx.strokeStyle = 'rgba(185, 154, 104, 0.55)';
    ctx.lineWidth = 2;
    ctx.stroke();

    const cx = x + w / 2 + 4;
    const cy = y + h / 2;
    ctx.strokeStyle = '#B99A68';
    ctx.lineWidth = 3;
    ctx.strokeRect(cx - 18, cy - 24, 36, 48);
    ctx.restore();
}

function drawImageCoverFit(ctx, img, x, y, w, h, radius = 14) {
    ctx.save();
    drawRoundedRect(ctx, x, y, w, h, radius);
    ctx.clip();

    const imgRatio = (img.width || 1) / (img.height || 1);
    const boxRatio = w / h;
    let drawW = w;
    let drawH = h;
    let offsetX = x;
    let offsetY = y;

    if (imgRatio > boxRatio) {
        drawW = h * imgRatio;
        offsetX = x - (drawW - w) / 2;
    } else {
        drawH = w / imgRatio;
        offsetY = y - (drawH - h) / 2;
    }

    ctx.drawImage(img, offsetX, offsetY, drawW, drawH);
    ctx.restore();

    ctx.save();
    drawRoundedRect(ctx, x, y, w, h, radius);
    ctx.lineWidth = 2;
    ctx.strokeStyle = 'rgba(185, 154, 104, 0.45)';
    ctx.stroke();
    ctx.restore();
}

export function initShareGenerator() {
    if (window.__QUOTEIT_SHARE_BOOTSTRAPPED__) return;

    const modal = document.getElementById('share-quote-modal');
    const canvas = document.getElementById('share-quote-canvas');
    if (!modal || !canvas) return;

    window.__QUOTEIT_SHARE_BOOTSTRAPPED__ = true;

    const ctx = canvas.getContext('2d');
    const loadingOverlay = document.getElementById('share-canvas-loading');

    const labels = {
        brandName: modal.getAttribute('data-brand-name') || 'ATHAR',
        brandSubtitle: modal.getAttribute('data-brand-subtitle') || '',
        quickQuote: modal.getAttribute('data-label-quick') || 'Quick Quote',
        bookQuote: modal.getAttribute('data-label-book') || 'Book Quote',
        publishedBy: modal.getAttribute('data-label-published-by') || 'Published by',
        attributedTo: modal.getAttribute('data-label-attributed-to') || 'Attributed to',
        fromBook: modal.getAttribute('data-label-from-book') || 'From book',
        msgDownloaded: modal.getAttribute('data-msg-downloaded') || 'PNG image downloaded successfully.',
        msgShared: modal.getAttribute('data-msg-shared') || 'Quote image shared successfully.',
        msgShareUnsupported: modal.getAttribute('data-msg-share-unsupported') || 'Direct file sharing is not supported on this browser. Please use Download PNG or Copy Quote Text.',
        msgCopied: modal.getAttribute('data-msg-copied') || 'Copied to clipboard',
        msgCopyError: modal.getAttribute('data-msg-copy-error') || 'Unable to copy quote',
    };

    let activeTemplate = 'editorial';
    let activeQuote = null;
    let cachedCoverImg = null;
    let cachedCoverUrl = '';

    function updateTemplateButtonsUI() {
        modal.querySelectorAll('[data-share-template]').forEach((btn) => {
            const tpl = btn.getAttribute('data-share-template');
            const isSelected = tpl === activeTemplate;
            btn.setAttribute('aria-checked', isSelected ? 'true' : 'false');

            if (isSelected) {
                btn.classList.add('border-[#4A3328]', 'dark:border-[#C9A66B]', 'bg-editorial-secondary/70', 'ring-2', 'ring-[#B99A68]/50');
                btn.classList.remove('border-editorial', 'bg-editorial-card');
            } else {
                btn.classList.remove('border-[#4A3328]', 'dark:border-[#C9A66B]', 'bg-editorial-secondary/70', 'ring-2', 'ring-[#B99A68]/50');
                btn.classList.add('border-editorial', 'bg-editorial-card');
            }
        });
    }

    async function renderShareCanvas(skipExternalCover = false) {
        if (!activeQuote || !ctx) return;

        if (loadingOverlay) loadingOverlay.classList.remove('hidden');

        try {
            if (document.fonts && document.fonts.ready) {
                await Promise.race([
                    document.fonts.ready,
                    new Promise((res) => setTimeout(res, 600)),
                ]);
            }
        } catch (e) {}

        const isBook = activeQuote.type === 'book';
        if (isBook && activeQuote.cover && !skipExternalCover) {
            if (cachedCoverUrl !== activeQuote.cover) {
                cachedCoverImg = await loadCoverImage(activeQuote.cover);
                cachedCoverUrl = activeQuote.cover;
            }
        } else {
            cachedCoverImg = null;
            if (skipExternalCover) cachedCoverUrl = '';
        }

        const palette = TEMPLATES[activeTemplate] || TEMPLATES.editorial;
        const quoteIsArabic = isArabicScript(activeQuote.text);
        const uiIsArabic = document.documentElement.lang === 'ar' || isArabicScript(labels.publishedBy);

        ctx.clearRect(0, 0, CANVAS_SIZE, CANVAS_SIZE);

        // 1. Outer Background
        ctx.fillStyle = palette.outerBg;
        ctx.fillRect(0, 0, CANVAS_SIZE, CANVAS_SIZE);

        // 2. Card Frame & Template-Specific Decoration
        const cardX = 64;
        const cardY = 64;
        const cardW = CANVAS_SIZE - 128;
        const cardH = CANVAS_SIZE - 128;

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

        // 3. Top Header Row (Brand Identity + Quote Type Pill)
        const contentPaddingX = 124;
        const leftX = contentPaddingX;
        const rightX = CANVAS_SIZE - contentPaddingX;

        const headerTextY = activeTemplate === 'magazine' ? cardY + 78 : cardY + 86;
        const brandTitleColor = activeTemplate === 'magazine' ? palette.headerText : palette.brandColor;

        ctx.save();
        ctx.direction = 'ltr';
        ctx.textAlign = 'left';
        ctx.fillStyle = brandTitleColor;
        ctx.font = `700 34px 'Lora', 'Tajawal', Georgia, serif`;
        ctx.fillText(labels.brandName, leftX, headerTextY);

        const badgeLabel = isBook ? labels.bookQuote : labels.quickQuote;
        ctx.font = `700 22px 'Tajawal', 'Inter', sans-serif`;
        const badgeTextWidth = ctx.measureText(badgeLabel).width;
        const badgeW = badgeTextWidth + 44;
        const badgeH = 46;
        const badgeX = rightX - badgeW;
        const badgeY = headerTextY - 32;

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

        // Decorative Opening Quotation Mark Watermark
        ctx.save();
        ctx.font = `700 150px Georgia, serif`;
        ctx.fillStyle = palette.accent;
        ctx.globalAlpha = activeTemplate === 'dark' ? 0.16 : 0.18;
        ctx.textAlign = quoteIsArabic ? 'right' : 'left';
        ctx.fillText('“', quoteIsArabic ? rightX : leftX, cardY + 255);
        ctx.restore();

        // 4. Quote Text Wrapping & Vertical Centering
        const quoteTopY = cardY + 185;
        const quoteBottomY = isBook ? (CANVAS_SIZE - 365) : (CANVAS_SIZE - 210);
        const availableQuoteHeight = quoteBottomY - quoteTopY;
        const maxQuoteWidth = rightX - leftX;

        const quoteFontFamily = quoteIsArabic
            ? `'Tajawal', sans-serif`
            : `'Lora', 'Tajawal', Georgia, serif`;

        const candidateSizes = [46, 42, 38, 34, 30, 26, 23];
        let chosenSize = 26;
        let wrappedLines = [];
        let lineHeight = 40;

        const decoratedQuoteText = `“${activeQuote.text}”`;

        for (const size of candidateSizes) {
            ctx.font = `600 ${size}px ${quoteFontFamily}`;
            const testLines = wrapTextLines(ctx, decoratedQuoteText, maxQuoteWidth);
            const testLineHeight = Math.round(size * (quoteIsArabic ? 1.58 : 1.48));
            if (testLines.length * testLineHeight <= availableQuoteHeight) {
                chosenSize = size;
                wrappedLines = testLines;
                lineHeight = testLineHeight;
                break;
            }
        }

        if (wrappedLines.length === 0) {
            chosenSize = 23;
            lineHeight = 35;
            ctx.font = `600 ${chosenSize}px ${quoteFontFamily}`;
            wrappedLines = wrapTextLines(ctx, decoratedQuoteText, maxQuoteWidth);
            const maxLines = Math.max(1, Math.floor(availableQuoteHeight / lineHeight));
            if (wrappedLines.length > maxLines) {
                wrappedLines = wrappedLines.slice(0, maxLines);
                wrappedLines[maxLines - 1] = truncateSingleLine(ctx, wrappedLines[maxLines - 1], maxQuoteWidth - 30);
            }
        }

        const totalTextHeight = wrappedLines.length * lineHeight;
        let currentLineY = quoteTopY + Math.max(chosenSize, (availableQuoteHeight - totalTextHeight) / 2 + chosenSize * 0.85);

        ctx.save();
        ctx.font = `600 ${chosenSize}px ${quoteFontFamily}`;
        ctx.fillStyle = palette.headingText;

        const centerAlign = activeTemplate === 'paper';
        ctx.direction = quoteIsArabic ? 'rtl' : 'ltr';
        ctx.textAlign = centerAlign ? 'center' : (quoteIsArabic ? 'right' : 'left');
        const textAnchorX = centerAlign ? (CANVAS_SIZE / 2) : (quoteIsArabic ? rightX : leftX);

        wrappedLines.forEach((line) => {
            if (line !== '') {
                ctx.fillText(line, textAnchorX, currentLineY);
            }
            currentLineY += lineHeight;
        });
        ctx.restore();

        // 5. Book Metadata Block (Only for Book Quotes)
        if (isBook) {
            const bookBoxY = CANVAS_SIZE - 340;
            const bookBoxH = 148;
            const bookBoxW = rightX - leftX;

            ctx.save();
            drawRoundedRect(ctx, leftX, bookBoxY, bookBoxW, bookBoxH, 20);
            ctx.fillStyle = palette.secondaryBg;
            ctx.fill();
            ctx.lineWidth = 1.5;
            ctx.strokeStyle = palette.border;
            ctx.stroke();
            ctx.restore();

            const coverW = 84;
            const coverH = 116;
            const coverY = bookBoxY + (bookBoxH - coverH) / 2;
            const coverX = uiIsArabic ? (rightX - 20 - coverW) : (leftX + 20);

            if (cachedCoverImg) {
                drawImageCoverFit(ctx, cachedCoverImg, coverX, coverY, coverW, coverH, 12);
            } else {
                drawBookFallbackCover(ctx, coverX, coverY, coverW, coverH, palette);
            }

            const metaMaxW = bookBoxW - coverW - 68;
            const metaAnchorX = uiIsArabic ? (coverX - 24) : (coverX + coverW + 24);

            ctx.save();
            ctx.direction = uiIsArabic ? 'rtl' : 'ltr';
            ctx.textAlign = uiIsArabic ? 'right' : 'left';

            ctx.font = `700 25px 'Tajawal', 'Inter', sans-serif`;
            ctx.fillStyle = palette.headingText;
            const bookTitleLine = `${labels.fromBook}: ${activeQuote.book || ''}`;
            ctx.fillText(truncateSingleLine(ctx, bookTitleLine, metaMaxW), metaAnchorX, bookBoxY + 62);

            ctx.font = `500 23px 'Tajawal', 'Inter', sans-serif`;
            ctx.fillStyle = palette.secondaryText;
            const authorLine = `${labels.attributedTo}: ${activeQuote.author || ''}`;
            ctx.fillText(truncateSingleLine(ctx, authorLine, metaMaxW), metaAnchorX, bookBoxY + 104);
            ctx.restore();
        }

        // 6. Footer Row: Platform Publisher & Brand Wordmark
        const footerDividerY = CANVAS_SIZE - 165;
        const footerTextY = CANVAS_SIZE - 115;

        ctx.save();
        ctx.beginPath();
        ctx.moveTo(leftX, footerDividerY);
        ctx.lineTo(rightX, footerDividerY);
        ctx.strokeStyle = palette.border;
        ctx.lineWidth = 1.5;
        ctx.stroke();

        const publisherText = `${labels.publishedBy}: ${activeQuote.publisher || ''}`;
        ctx.font = `600 23px 'Tajawal', 'Inter', sans-serif`;
        ctx.fillStyle = palette.headingText;
        ctx.direction = uiIsArabic ? 'rtl' : 'ltr';
        ctx.textAlign = uiIsArabic ? 'right' : 'left';
        const pubX = uiIsArabic ? rightX : leftX;
        ctx.fillText(truncateSingleLine(ctx, publisherText, (rightX - leftX) * 0.62), pubX, footerTextY);

        ctx.font = `600 21px 'Lora', 'Tajawal', sans-serif`;
        ctx.fillStyle = palette.accent;
        ctx.direction = 'ltr';
        ctx.textAlign = uiIsArabic ? 'left' : 'right';
        const brandFooterX = uiIsArabic ? leftX : rightX;
        ctx.fillText(labels.brandName, brandFooterX, footerTextY);
        ctx.restore();

        if (loadingOverlay) loadingOverlay.classList.add('hidden');
    }

    function extractCanvasBlob() {
        return new Promise((resolve, reject) => {
            try {
                if (canvas.toBlob) {
                    canvas.toBlob((blob) => {
                        if (blob) resolve(blob);
                        else reject(new Error('Empty blob'));
                    }, 'image/png', 1.0);
                } else {
                    const dataUrl = canvas.toDataURL('image/png');
                    const parts = dataUrl.split(',');
                    const byteString = atob(parts[1]);
                    const mimeString = parts[0].split(':')[1].split(';')[0];
                    const ab = new ArrayBuffer(byteString.length);
                    const ia = new Uint8Array(ab);
                    for (let i = 0; i < byteString.length; i++) {
                        ia[i] = byteString.charCodeAt(i);
                    }
                    resolve(new Blob([ab], { type: mimeString }));
                }
            } catch (err) {
                reject(err);
            }
        });
    }

    async function getSafePngBlob() {
        await renderShareCanvas(false);
        try {
            return await extractCanvasBlob();
        } catch (err) {
            // If an external image tainted the canvas, re-render with vector fallback cover
            await renderShareCanvas(true);
            return await extractCanvasBlob();
        }
    }

    async function downloadPng() {
        if (!activeQuote) return;
        try {
            const blob = await getSafePngBlob();
            if (!blob) return;

            const filename = `quoteit-quote-${activeQuote.id || 'card'}-${activeTemplate}.png`;
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = filename;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            setTimeout(() => URL.revokeObjectURL(url), 3000);

            window.QuoteItUI?.showToast(labels.msgDownloaded, 'success');
        } catch (e) {
            window.QuoteItUI?.showToast(labels.msgCopyError, 'error');
        }
    }

    async function shareNativePng() {
        if (!activeQuote) return;
        try {
            const blob = await getSafePngBlob();
            if (!blob) return;

            const filename = `quoteit-quote-${activeQuote.id || 'card'}-${activeTemplate}.png`;
            const file = new File([blob], filename, { type: 'image/png' });

            if (navigator.canShare && navigator.canShare({ files: [file] })) {
                try {
                    await navigator.share({
                        title: labels.brandName,
                        text: `"${activeQuote.text}"`,
                        files: [file],
                    });
                    window.QuoteItUI?.showToast(labels.msgShared, 'success');
                    return;
                } catch (err) {
                    if (err && err.name === 'AbortError') return;
                }
            }

            window.QuoteItUI?.showToast(labels.msgShareUnsupported, 'error');
        } catch (e) {
            window.QuoteItUI?.showToast(labels.msgShareUnsupported, 'error');
        }
    }

    function copyActiveQuoteText() {
        if (!activeQuote) return;
        const attribution = activeQuote.type === 'book'
            ? `${activeQuote.author}${activeQuote.book ? ` (${activeQuote.book})` : ''}`
            : activeQuote.publisher;
        const formatted = attribution ? `"${activeQuote.text}" — ${attribution}` : activeQuote.text;

        window.QuoteItUI?.copyQuoteText(formatted, labels.msgCopied, labels.msgCopyError);
    }

    // Use document-level delegation so dynamic cards and modal controls always respond
    document.addEventListener('click', (e) => {
        const shareTrigger = e.target.closest('[data-share-quote]');
        if (shareTrigger) {
            e.preventDefault();
            e.stopPropagation();
            const quoteId = shareTrigger.getAttribute('data-share-quote');
            const card = shareTrigger.closest('[data-quote-card]') || document.getElementById(`quote-card-${quoteId}`);
            if (!card) return;

            activeQuote = {
                id: card.getAttribute('data-quote-id') || quoteId,
                type: card.getAttribute('data-quote-type') || 'quick',
                text: card.getAttribute('data-quote-text') || '',
                publisher: card.getAttribute('data-quote-publisher') || '',
                author: card.getAttribute('data-quote-author') || '',
                book: card.getAttribute('data-quote-book') || '',
                cover: card.getAttribute('data-quote-cover') || '',
            };

            updateTemplateButtonsUI();
            window.QuoteItUI?.openModal('share-quote-modal');
            renderShareCanvas(false);
            return;
        }

        const templateBtn = e.target.closest('[data-share-template]');
        if (templateBtn && modal.contains(templateBtn)) {
            e.preventDefault();
            activeTemplate = templateBtn.getAttribute('data-share-template') || 'editorial';
            updateTemplateButtonsUI();
            renderShareCanvas(false);
            return;
        }

        const dlBtn = e.target.closest('#share-download-png-btn');
        if (dlBtn) {
            e.preventDefault();
            downloadPng();
            return;
        }

        const nativeBtn = e.target.closest('#share-native-file-btn');
        if (nativeBtn) {
            e.preventDefault();
            shareNativePng();
            return;
        }

        const copyBtn = e.target.closest('#share-copy-text-btn');
        if (copyBtn) {
            e.preventDefault();
            copyActiveQuoteText();
        }
    });
}