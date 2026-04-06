/**
 * Universal @mention autocomplete for RichEditor (contenteditable) and textarea
 */
(function() {
    const isDark = () => document.documentElement.classList.contains('dark');
    let dropdown = null;
    let activeElement = null;
    let mentionStart = -1;
    let selectedIndex = 0;
    let users = [];

    function getUsers() {
        // Try window.mentionUsers first (ticket view), then data attribute
        if (window.mentionUsers && Array.isArray(window.mentionUsers) && window.mentionUsers.length > 0) {
            return window.mentionUsers;
        }
        return [];
    }

    function createDropdown() {
        if (dropdown) return;
        dropdown = document.createElement('div');
        dropdown.id = 'mention-dropdown-global';
        dropdown.style.cssText = 'position:fixed;border-radius:8px;max-height:220px;overflow-y:auto;z-index:99999;display:none;min-width:220px;';
        updateDropdownTheme();
        document.body.appendChild(dropdown);
    }

    function updateDropdownTheme() {
        if (!dropdown) return;
        const dark = isDark();
        dropdown.style.background = dark ? '#1f2937' : '#ffffff';
        dropdown.style.border = '1px solid ' + (dark ? '#374151' : '#e5e7eb');
        dropdown.style.boxShadow = '0 4px 12px rgba(0,0,0,' + (dark ? '0.5' : '0.15') + ')';
    }

    function filterUsers(query) {
        const q = (query || '').toLowerCase();
        return users.filter(u =>
            (u.name && u.name.toLowerCase().includes(q)) ||
            (u.username && u.username.toLowerCase().includes(q))
        ).slice(0, 6);
    }

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str || '';
        return div.innerHTML;
    }

    function renderDropdown(filtered) {
        if (!dropdown) return;
        const dark = isDark();
        const hoverBg = dark ? '#374151' : '#f3f4f6';
        const textC = dark ? '#f3f4f6' : '#111827';
        const subC = dark ? '#9ca3af' : '#6b7280';
        const borderC = dark ? '#374151' : '#f3f4f6';

        updateDropdownTheme();

        dropdown.innerHTML = filtered.map((u, i) => `
            <div class="m-item" data-index="${i}" data-username="${escapeHtml(u.username)}"
                 style="padding:8px 12px;cursor:pointer;display:flex;align-items:center;gap:8px;background:${i === selectedIndex ? hoverBg : 'transparent'};border-bottom:1px solid ${borderC};">
                <img src="${u.avatar || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(u.name) + '&size=32'}"
                     style="width:28px;height:28px;border-radius:50%;object-fit:cover;flex-shrink:0;"
                     onerror="this.src='https://ui-avatars.com/api/?name=${encodeURIComponent(u.name)}&size=32'">
                <div style="flex:1;min-width:0;">
                    <div style="font-weight:500;font-size:13px;color:${textC};overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${escapeHtml(u.name)}</div>
                    <div style="font-size:11px;color:${subC};">@${escapeHtml(u.username)}</div>
                </div>
            </div>
        `).join('');

        dropdown.querySelectorAll('.m-item').forEach(item => {
            item.addEventListener('mousedown', function(e) {
                e.preventDefault();
                e.stopPropagation();
                insertMention(this.dataset.username);
            });
            item.addEventListener('mouseenter', function() {
                selectedIndex = parseInt(this.dataset.index);
                highlightItems();
            });
        });
    }

    function highlightItems() {
        if (!dropdown) return;
        const dark = isDark();
        const hoverBg = dark ? '#374151' : '#f3f4f6';
        dropdown.querySelectorAll('.m-item').forEach((item, i) => {
            item.style.background = i === selectedIndex ? hoverBg : 'transparent';
        });
    }

    function showDropdown(anchorEl) {
        if (!dropdown) return;
        const rect = anchorEl.getBoundingClientRect();
        dropdown.style.display = 'block';
        // Position above the element
        dropdown.style.left = rect.left + 'px';
        dropdown.style.top = (rect.top - dropdown.offsetHeight - 4) + 'px';
        // If goes off screen top, put below
        requestAnimationFrame(() => {
            const dRect = dropdown.getBoundingClientRect();
            if (dRect.top < 0) {
                dropdown.style.top = (rect.bottom + 4) + 'px';
            }
        });
    }

    function hideDropdown() {
        if (dropdown) dropdown.style.display = 'none';
        mentionStart = -1;
        selectedIndex = 0;
        activeElement = null;
    }

    // Get text and cursor position from either textarea or contenteditable
    function getTextAndCursor(el) {
        if (el.tagName === 'TEXTAREA' || el.tagName === 'INPUT') {
            return { text: el.value, cursor: el.selectionStart };
        }
        // Contenteditable
        const text = el.innerText || el.textContent || '';
        const sel = window.getSelection();
        if (!sel.rangeCount) return { text, cursor: text.length };
        const range = sel.getRangeAt(0);
        const pre = range.cloneRange();
        pre.selectNodeContents(el);
        pre.setEnd(range.endContainer, range.endOffset);
        return { text, cursor: pre.toString().length };
    }

    function insertMention(username) {
        if (!activeElement || mentionStart === -1) return;
        const el = activeElement;
        const isTextarea = el.tagName === 'TEXTAREA' || el.tagName === 'INPUT';

        if (isTextarea) {
            const val = el.value;
            const before = val.substring(0, mentionStart);
            const after = val.substring(el.selectionStart);
            el.value = before + '@' + username + ' ' + after;
            const newPos = mentionStart + username.length + 2;
            el.setSelectionRange(newPos, newPos);
            el.dispatchEvent(new Event('input', { bubbles: true }));
        } else {
            // Contenteditable - insert as text
            const text = el.innerText || '';
            const { cursor } = getTextAndCursor(el);
            const before = text.substring(0, mentionStart);
            const after = text.substring(cursor);
            el.innerText = before + '@' + username + ' ' + after;
            // Set cursor
            try {
                const range = document.createRange();
                const sel = window.getSelection();
                const newPos = mentionStart + username.length + 2;
                const node = el.firstChild || el;
                range.setStart(node, Math.min(newPos, node.length || 0));
                range.collapse(true);
                sel.removeAllRanges();
                sel.addRange(range);
            } catch(e) {}
            el.dispatchEvent(new Event('input', { bubbles: true }));
        }

        el.focus();
        hideDropdown();
    }

    function handleInput(el) {
        const { text, cursor } = getTextAndCursor(el);

        // Find @ before cursor
        let atIdx = -1;
        for (let i = cursor - 1; i >= 0; i--) {
            if (text[i] === '@') { atIdx = i; break; }
            if (text[i] === ' ' || text[i] === '\n') break;
        }

        if (atIdx !== -1) {
            mentionStart = atIdx;
            activeElement = el;
            const query = text.substring(atIdx + 1, cursor);
            const filtered = filterUsers(query);
            if (filtered.length > 0) {
                selectedIndex = Math.min(selectedIndex, filtered.length - 1);
                renderDropdown(filtered);
                showDropdown(el);
            } else {
                hideDropdown();
            }
        } else {
            hideDropdown();
        }
    }

    function handleKeydown(e) {
        if (!dropdown || dropdown.style.display !== 'block') return;
        const items = dropdown.querySelectorAll('.m-item');
        if (!items.length) return;

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            selectedIndex = (selectedIndex + 1) % items.length;
            highlightItems();
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            selectedIndex = selectedIndex === 0 ? items.length - 1 : selectedIndex - 1;
            highlightItems();
        } else if (e.key === 'Enter' || e.key === 'Tab') {
            if (dropdown.style.display === 'block') {
                e.preventDefault();
                e.stopPropagation();
                const sel = items[selectedIndex];
                if (sel) insertMention(sel.dataset.username);
            }
        } else if (e.key === 'Escape') {
            hideDropdown();
        }
    }

    function attachToElement(el) {
        if (el.dataset._mentionBound) return;
        el.dataset._mentionBound = 'true';

        el.addEventListener('keyup', () => handleInput(el));
        el.addEventListener('keydown', handleKeydown);
        el.addEventListener('click', () => handleInput(el));
    }

    function scanAndAttach() {
        users = getUsers();
        if (!users.length) return;

        createDropdown();

        // 1. Find all RichEditor contenteditable elements
        document.querySelectorAll('[data-enable-mentions="true"]').forEach(wrapper => {
            // Try to find the actual editable element inside
            const selectors = ['.trix-content', '[contenteditable="true"]', '.ProseMirror', '.ql-editor', 'textarea'];
            for (const sel of selectors) {
                const el = wrapper.querySelector(sel);
                if (el) { attachToElement(el); break; }
            }
        });

        // 2. Find specific discussion textarea
        const discussionTa = document.getElementById('discussion-reply-textarea');
        if (discussionTa) attachToElement(discussionTa);
    }

    // Close dropdown on outside click
    document.addEventListener('click', function(e) {
        if (dropdown && !dropdown.contains(e.target)) {
            const isActiveEl = activeElement && activeElement.contains(e.target);
            if (!isActiveEl) hideDropdown();
        }
    });

    // Run on page load and Livewire updates
    document.addEventListener('DOMContentLoaded', () => setTimeout(scanAndAttach, 500));
    document.addEventListener('livewire:load', () => setTimeout(scanAndAttach, 500));
    document.addEventListener('livewire:update', () => setTimeout(scanAndAttach, 300));

    // Also try after a delay for dynamic content
    setTimeout(scanAndAttach, 1000);
    setTimeout(scanAndAttach, 2000);
})();
