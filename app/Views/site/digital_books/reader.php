<?= $this->extend('site/layouts/main') ?>

<?= $this->section('content') ?>
<?php
$reader = is_array($reader ?? null) ? $reader : [];
$book = is_array($reader['book'] ?? null) ? $reader['book'] : [];
$currentPage = (int) ($reader['current_page'] ?? 1);
$totalPages = (int) ($reader['total_pages'] ?? 0);
$hasPrev = (bool) ($reader['has_prev'] ?? false);
$hasNext = (bool) ($reader['has_next'] ?? false);
$isEmpty = (bool) ($reader['is_empty'] ?? true);
$content = (string) ($reader['content'] ?? '');
$productId = (string) ($book['product_id'] ?? '');
$baseReadUrl = base_url('yardim/dijital-kitaplarim/' . $productId . '/oku');
$highlightsBaseUrl = base_url('api/digital-books/' . $productId . '/highlights');
?>
<style>
    .reader-page { max-width: 1100px; margin: 0 auto; padding: 1.2rem 1rem 2.2rem; }
    .reader-top { display: flex; align-items: center; justify-content: space-between; gap: 1rem; margin-bottom: 1rem; flex-wrap: wrap; }
    .reader-back { color: #334155; text-decoration: none; font-weight: 700; }
    .reader-toggle { min-height: 36px; border: 1px solid rgba(148,163,184,.35); border-radius: 10px; padding: .35rem .8rem; background: #fff; color: #0f172a; font-size: .83rem; font-weight: 700; cursor: pointer; }
    .reader-toggle.is-on { border-color: rgba(22,119,255,.45); background: rgba(22,119,255,.1); color: #0f52ba; }
    .reader-card { border: 1px solid rgba(148,163,184,.22); border-radius: 18px; background: #fff; box-shadow: 0 14px 28px rgba(15,23,42,.06); overflow: hidden; }
    .reader-head { padding: 1rem 1.1rem; border-bottom: 1px solid rgba(148,163,184,.16); background: #f8fbff; }
    .reader-title { margin: 0; color: #0f172a; font-size: 1.15rem; font-weight: 800; }
    .reader-author { margin: .35rem 0 0; color: #64748b; font-size: .9rem; }
    .reader-body { padding: 1.15rem; min-height: 380px; background: #fff; }
    .reader-content-wrap { position: relative; }
    .reader-content-wrap::after {
        content: attr(data-watermark);
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: clamp(1.1rem, 2vw, 1.5rem);
        color: rgba(100, 116, 139, 0.14);
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        pointer-events: none;
        user-select: none;
    }
    .reader-content {
        position: relative;
        z-index: 1;
        margin: 0;
        color: #111827;
        line-height: 1.9;
        white-space: pre-wrap;
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
        cursor: default;
    }
    .reader-content,
    .reader-content * {
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
    }
    .reader-content.highlight-mode,
    .reader-content.highlight-mode * {
        -webkit-user-select: text !important;
        -moz-user-select: text !important;
        -ms-user-select: text !important;
        user-select: text !important;
    }
    .reader-content.highlight-mode { cursor: text; }
    .reader-empty { margin: 0; color: #475569; line-height: 1.7; }
    .reader-foot { padding: .9rem 1.1rem 1.1rem; border-top: 1px solid rgba(148,163,184,.16); display: flex; align-items: center; justify-content: space-between; gap: .75rem; flex-wrap: wrap; }
    .reader-nav { display: inline-flex; gap: .5rem; }
    .reader-btn { display: inline-flex; align-items: center; justify-content: center; min-height: 38px; padding: .45rem .85rem; border-radius: 10px; border: 1px solid rgba(148,163,184,.28); color: #0f172a; text-decoration: none; font-size: .84rem; font-weight: 700; background: #fff; }
    .reader-btn.is-disabled { opacity: .45; pointer-events: none; }
    .reader-page-meta { color: #334155; font-size: .86rem; font-weight: 700; }
    .reader-highlight-actions { margin-top: .85rem; display: none; gap: .5rem; align-items: center; flex-wrap: wrap; }
    .reader-highlight-actions.is-visible { display: flex; }
    .reader-highlight-preview { font-size: .82rem; color: #475569; }
    .reader-highlight-save { min-height: 34px; border: 1px solid rgba(22,119,255,.45); border-radius: 9px; background: rgba(22,119,255,.1); color: #0f52ba; font-size: .82rem; font-weight: 700; padding: .3rem .75rem; cursor: pointer; }
    mark.reader-mark { padding: 0 .1rem; border-radius: .2rem; }
    mark.reader-mark.reader-mark-yellow { background: rgba(250, 204, 21, .45); }
    mark.reader-mark.reader-mark-green { background: rgba(34, 197, 94, .28); }
    mark.reader-mark.reader-mark-blue { background: rgba(59, 130, 246, .28); }
    mark.reader-mark.reader-mark-pink { background: rgba(244, 114, 182, .3); }
    mark.reader-mark { cursor: pointer; }
    .reader-highlight-popover {
        position: fixed;
        z-index: 1200;
        min-width: 132px;
        max-width: calc(100vw - 24px);
        border: 1px solid rgba(148,163,184,.25);
        border-radius: 10px;
        background: #fff;
        box-shadow: 0 14px 28px rgba(15,23,42,.12);
        padding: .5rem;
        display: none;
    }
    .reader-highlight-popover.is-open { display: block; }
    .reader-highlight-popover-title {
        margin: 0 0 .35rem;
        color: #475569;
        font-size: .76rem;
        font-weight: 700;
    }
    .reader-highlight-popover-delete {
        width: 100%;
        min-height: 32px;
        border: 1px solid rgba(220,38,38,.28);
        border-radius: 8px;
        background: rgba(220,38,38,.06);
        color: #b91c1c;
        font-size: .78rem;
        font-weight: 700;
        cursor: pointer;
    }
</style>

<section class="reader-page">
    <div class="reader-top">
        <a href="<?= esc(base_url('yardim/dijital-kitaplarim')) ?>" class="reader-back">Dijital Kitaplarim'a Don</a>
        <?php if (! $isEmpty): ?>
            <button type="button" class="reader-toggle" id="highlightModeToggle">Isaretleme Modu: Kapali</button>
        <?php endif; ?>
    </div>

    <article class="reader-card">
        <header class="reader-head">
            <h1 class="reader-title"><?= esc((string) ($book['title'] ?? 'Dijital Kitap')) ?></h1>
            <p class="reader-author"><?= esc((string) ($book['author'] ?? 'Yazar belirtilmedi')) ?></p>
        </header>

        <div class="reader-body">
            <?php if ($isEmpty): ?>
                <p class="reader-empty">Bu kitap icin okunabilir icerik henuz hazir degil.</p>
            <?php else: ?>
                <div class="reader-content-wrap" data-watermark="Kitap Dunyasi Dijital Okuma">
                    <p class="reader-content" id="readerContent" tabindex="0"></p>
                </div>
                <div class="reader-highlight-actions" id="highlightActions">
                    <span class="reader-highlight-preview" id="highlightPreview"></span>
                    <button type="button" class="reader-highlight-save" id="saveHighlightButton">Highlight Kaydet</button>
                </div>
            <?php endif; ?>
        </div>

        <footer class="reader-foot">
            <div class="reader-nav">
                <a href="<?= esc($baseReadUrl . '?page=' . max(1, $currentPage - 1)) ?>" class="reader-btn<?= $hasPrev ? '' : ' is-disabled' ?>">Onceki</a>
                <a href="<?= esc($baseReadUrl . '?page=' . ($currentPage + 1)) ?>" class="reader-btn<?= $hasNext ? '' : ' is-disabled' ?>">Sonraki</a>
            </div>
            <div class="reader-page-meta">
                <?php if ($totalPages > 0): ?>
                    Sayfa <?= esc((string) $currentPage) ?> / <?= esc((string) $totalPages) ?>
                <?php else: ?>
                    Sayfa bilgisi bulunmuyor
                <?php endif; ?>
            </div>
        </footer>
    </article>
</section>
<div class="reader-highlight-popover" id="highlightPopover" role="dialog" aria-hidden="true">
    <p class="reader-highlight-popover-title">Isaret</p>
    <button type="button" class="reader-highlight-popover-delete" id="highlightPopoverDelete">Isareti Sil</button>
</div>
<?php if (! $isEmpty): ?>
<script>
(() => {
    const contentEl = document.getElementById('readerContent');
    const toggleEl = document.getElementById('highlightModeToggle');
    const actionsEl = document.getElementById('highlightActions');
    const previewEl = document.getElementById('highlightPreview');
    const saveButton = document.getElementById('saveHighlightButton');
    const popoverEl = document.getElementById('highlightPopover');
    const popoverDeleteEl = document.getElementById('highlightPopoverDelete');
    if (!contentEl || !toggleEl || !actionsEl || !previewEl || !saveButton || !popoverEl || !popoverDeleteEl) return;

    const productId = <?= json_encode($productId, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const pageNo = <?= (int) $currentPage ?>;
    const highlightsUrl = <?= json_encode($highlightsBaseUrl, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const originalText = <?= json_encode($content, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    let highlightMode = false;
    let currentSelection = null;
    let highlights = [];
    let activeHighlightId = null;

    const blockedEvents = ['copy', 'cut', 'dragstart', 'contextmenu'];
    blockedEvents.forEach((eventName) => {
        contentEl.addEventListener(eventName, (event) => {
            event.preventDefault();
        });
    });
    contentEl.addEventListener('selectstart', (event) => {
        if (!highlightMode) event.preventDefault();
    });
    contentEl.addEventListener('keydown', (event) => {
        const key = String(event.key || '').toLowerCase();
        const withModifier = event.ctrlKey || event.metaKey;
        if (!withModifier) return;
        if (key === 'c' || key === 'x' || key === 'a' || key === 's') {
            event.preventDefault();
        }
    });

    function escapeHtml(text) {
        return String(text)
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#39;');
    }

    function clampColor(color) {
        const safe = String(color || '').toLowerCase();
        if (safe === 'green' || safe === 'blue' || safe === 'pink') return safe;
        return 'yellow';
    }

    function normalizeText(text) {
        return String(text || '').trim().replace(/\s+/g, ' ');
    }

    function buildRanges(text, items) {
        const ranges = [];
        const used = [];

        const sorted = [...items].sort((a, b) => String(a.created_at || '').localeCompare(String(b.created_at || '')));
        for (const item of sorted) {
            const selectedText = normalizeText(item.selected_text);
            const start = Number.isInteger(item.start_offset) ? item.start_offset : parseInt(item.start_offset, 10);
            const end = Number.isInteger(item.end_offset) ? item.end_offset : parseInt(item.end_offset, 10);
            if (!(start >= 0 && end > start && end <= text.length)) continue;
            const sliced = normalizeText(text.slice(start, end));
            if (!selectedText || sliced !== selectedText) continue;

            const overlaps = used.some((range) => !(end <= range.start || start >= range.end));
            if (overlaps) continue;

            used.push({ start, end });
            ranges.push({ start, end, id: String(item.id || ''), color: clampColor(item.color) });
        }

        return ranges.sort((a, b) => a.start - b.start);
    }

    function getTextOffset(root, container, localOffset) {
        const walker = document.createTreeWalker(root, NodeFilter.SHOW_TEXT);
        let total = 0;
        let node = walker.nextNode();
        while (node) {
            if (node === container) {
                return total + localOffset;
            }
            total += node.textContent ? node.textContent.length : 0;
            node = walker.nextNode();
        }
        return -1;
    }

    function resolveSelectionOffsets(root, selectionRange) {
        if (!selectionRange) return null;

        let start = getTextOffset(root, selectionRange.startContainer, selectionRange.startOffset);
        let end = getTextOffset(root, selectionRange.endContainer, selectionRange.endOffset);

        if (start < 0 || end < 0) {
            return null;
        }
        if (end < start) {
            const tmp = start;
            start = end;
            end = tmp;
        }
        if (end === start) {
            return null;
        }

        return { start, end };
    }

    function renderContent() {
        const text = String(originalText || '');
        if (!text) {
            contentEl.textContent = '';
            return;
        }

        const ranges = buildRanges(text, highlights);
        if (ranges.length === 0) {
            contentEl.textContent = text;
            return;
        }

        let cursor = 0;
        let html = '';
        for (const range of ranges) {
            if (range.start > cursor) {
                html += escapeHtml(text.slice(cursor, range.start));
            }
            const part = text.slice(range.start, range.end);
            html += `<mark class="reader-mark reader-mark-${range.color}" data-highlight-id="${escapeHtml(range.id)}">${escapeHtml(part)}</mark>`;
            cursor = range.end;
        }
        if (cursor < text.length) {
            html += escapeHtml(text.slice(cursor));
        }
        contentEl.innerHTML = html;
    }

    async function loadHighlights() {
        const response = await fetch(`${highlightsUrl}?page=${pageNo}`, {
            method: 'GET',
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin',
        });
        if (!response.ok) {
            highlights = [];
            renderContent();
            closePopover();
            return;
        }
        const data = await response.json();
        highlights = Array.isArray(data.highlights) ? data.highlights : [];
        renderContent();
        closePopover();
    }

    function clearSelectionUi() {
        currentSelection = null;
        actionsEl.classList.remove('is-visible');
        previewEl.textContent = '';
        const selection = window.getSelection();
        if (selection) selection.removeAllRanges();
    }
    function closePopover() {
        activeHighlightId = null;
        popoverEl.classList.remove('is-open');
        popoverEl.setAttribute('aria-hidden', 'true');
    }

    function openPopoverForMark(markEl) {
        const highlightId = String(markEl.getAttribute('data-highlight-id') || '');
        if (!highlightId) return;

        activeHighlightId = highlightId;
        const rect = markEl.getBoundingClientRect();
        const preferredTop = rect.top - 56;
        const safeTop = preferredTop < 8 ? rect.bottom + 8 : preferredTop;
        const left = Math.min(Math.max(8, rect.left), window.innerWidth - 148);

        popoverEl.style.top = `${safeTop}px`;
        popoverEl.style.left = `${left}px`;
        popoverEl.classList.add('is-open');
        popoverEl.setAttribute('aria-hidden', 'false');
    }

    function setHighlightMode(active) {
        highlightMode = !!active;
        contentEl.classList.toggle('highlight-mode', highlightMode);
        toggleEl.classList.toggle('is-on', highlightMode);
        toggleEl.textContent = `Isaretleme Modu: ${highlightMode ? 'Acik' : 'Kapali'}`;
        if (!highlightMode) clearSelectionUi();
    }

    contentEl.addEventListener('mouseup', () => {
        if (!highlightMode) return;
        const selection = window.getSelection();
        if (!selection || selection.rangeCount === 0) {
            clearSelectionUi();
            return;
        }
        const selectedText = String(selection.toString() || '').trim();
        if (!selectedText) {
            clearSelectionUi();
            return;
        }
        if (!contentEl.contains(selection.anchorNode) || !contentEl.contains(selection.focusNode)) {
            clearSelectionUi();
            return;
        }
        const range = selection.getRangeAt(0);
        const offsets = resolveSelectionOffsets(contentEl, range);
        if (!offsets) {
            previewEl.textContent = 'Secim ofseti hesaplanamadi.';
            setTimeout(() => clearSelectionUi(), 900);
            return;
        }

        currentSelection = {
            selected_text: selectedText,
            start_offset: offsets.start,
            end_offset: offsets.end,
        };
        previewEl.textContent = `Secilen: "${selectedText.slice(0, 80)}${selectedText.length > 80 ? '...' : ''}"`;
        actionsEl.classList.add('is-visible');
    });
    contentEl.addEventListener('click', (event) => {
        const mark = event.target.closest('mark.reader-mark');
        if (!mark) return;
        event.preventDefault();
        openPopoverForMark(mark);
    });

    toggleEl.addEventListener('click', () => {
        setHighlightMode(!highlightMode);
    });

    saveButton.addEventListener('click', async () => {
        if (!currentSelection) return;
        const normalizedSelected = normalizeText(currentSelection.selected_text);
        const duplicateLocal = highlights.some((item) => {
            const itemText = normalizeText(item.selected_text);
            const itemStart = Number.isInteger(item.start_offset) ? item.start_offset : parseInt(item.start_offset, 10);
            const itemEnd = Number.isInteger(item.end_offset) ? item.end_offset : parseInt(item.end_offset, 10);
            return (
                itemText === normalizedSelected &&
                itemStart === currentSelection.start_offset &&
                itemEnd === currentSelection.end_offset
            );
        });
        if (duplicateLocal) {
            previewEl.textContent = 'Bu pasaj zaten isaretli.';
            setTimeout(() => clearSelectionUi(), 900);
            return;
        }
        const payload = {
            page_no: pageNo,
            selected_text: normalizedSelected,
            start_offset: currentSelection.start_offset,
            end_offset: currentSelection.end_offset,
            color: 'yellow',
        };
        const response = await fetch(highlightsUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            credentials: 'same-origin',
            body: JSON.stringify(payload),
        });
        if (response.ok || response.status === 409) {
            await loadHighlights();
            if (response.status === 409) {
                previewEl.textContent = 'Bu pasaj zaten isaretli.';
                setTimeout(() => clearSelectionUi(), 900);
                return;
            }
        }
        clearSelectionUi();
    });
    popoverDeleteEl.addEventListener('click', async () => {
        if (!activeHighlightId) return;
        const response = await fetch(`${highlightsUrl}/${encodeURIComponent(activeHighlightId)}`, {
            method: 'DELETE',
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin',
        });
        if (response.ok) {
            await loadHighlights();
            return;
        }
        closePopover();
    });
    document.addEventListener('click', (event) => {
        if (!popoverEl.classList.contains('is-open')) return;
        const clickedInsidePopover = popoverEl.contains(event.target);
        const clickedMark = event.target.closest('mark.reader-mark');
        if (clickedInsidePopover || clickedMark) return;
        closePopover();
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closePopover();
        }
    });

    setHighlightMode(false);
    loadHighlights();
})();
</script>
<?php endif; ?>
<?= $this->endSection() ?>
