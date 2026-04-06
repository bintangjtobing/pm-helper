/**
 * Real-time toast notifications via Pusher
 * Shows a popup at top-right with 5-second progress bar
 */
(function() {
    // Create toast container
    const container = document.createElement('div');
    container.id = 'toast-container';
    container.style.cssText = 'position:fixed;top:16px;right:16px;z-index:99999;display:flex;flex-direction:column;gap:8px;max-width:380px;width:100%;pointer-events:none;';
    document.body.appendChild(container);

    function isDark() {
        return document.documentElement.classList.contains('dark');
    }

    function showToast(title, body, url) {
        const dark = isDark();
        const toast = document.createElement('div');
        toast.style.cssText = `
            background:${dark ? '#1f2937' : '#ffffff'};
            border:1px solid ${dark ? '#374151' : '#e5e7eb'};
            border-radius:8px;
            box-shadow:0 4px 12px rgba(0,0,0,${dark ? '0.5' : '0.15'});
            padding:14px 16px 10px 16px;
            pointer-events:auto;
            cursor:${url ? 'pointer' : 'default'};
            transform:translateX(120%);
            transition:transform 0.3s ease, opacity 0.3s ease;
            opacity:0;
            position:relative;
            overflow:hidden;
        `;

        // Bell icon
        const iconSvg = `<svg style="width:18px;height:18px;color:#3b82f6;flex-shrink:0;" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z"/></svg>`;

        toast.innerHTML = `
            <div style="display:flex;align-items:flex-start;gap:10px;">
                ${iconSvg}
                <div style="flex:1;min-width:0;">
                    <div style="font-weight:600;font-size:13px;color:${dark ? '#f3f4f6' : '#111827'};margin-bottom:2px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${escapeHtml(title)}</div>
                    <div style="font-size:12px;color:${dark ? '#9ca3af' : '#6b7280'};overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${escapeHtml(body)}</div>
                </div>
                <button onclick="this.closest('div[style]').remove()" style="color:${dark ? '#6b7280' : '#9ca3af'};background:none;border:none;cursor:pointer;padding:0;font-size:16px;line-height:1;">&times;</button>
            </div>
            <div style="position:absolute;bottom:0;left:0;right:0;height:3px;background:${dark ? '#374151' : '#e5e7eb'};">
                <div class="toast-progress" style="height:100%;background:#3b82f6;width:100%;transition:width 5s linear;border-radius:0 0 0 8px;"></div>
            </div>
        `;

        if (url) {
            toast.addEventListener('click', function(e) {
                if (e.target.tagName !== 'BUTTON') {
                    window.location.href = url;
                }
            });
        }

        container.appendChild(toast);

        // Animate in
        requestAnimationFrame(() => {
            toast.style.transform = 'translateX(0)';
            toast.style.opacity = '1';
            // Start progress bar countdown
            requestAnimationFrame(() => {
                const bar = toast.querySelector('.toast-progress');
                if (bar) bar.style.width = '0%';
            });
        });

        // Auto dismiss after 5 seconds
        setTimeout(() => {
            toast.style.transform = 'translateX(120%)';
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 300);
        }, 5000);
    }

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str || '';
        return div.innerHTML;
    }

    // Poll for new notifications (works without Pusher too)
    let lastCount = -1;

    function checkNotifications() {
        fetch('/api/user', { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .catch(() => {}); // Ignore auth errors
    }

    // Listen for Filament notification events
    document.addEventListener('filament-notification', function(e) {
        const data = e.detail;
        if (data && data.title) {
            showToast(data.title, data.body || '', data.url || '');
        }
    });

    // Listen for Livewire events (Filament v2 database notifications)
    if (window.Livewire) {
        Livewire.hook('message.processed', (message, component) => {
            // Check if new notifications appeared
            const badge = document.querySelector('[x-text="unreadNotificationsCount"]');
            if (badge) {
                const count = parseInt(badge.textContent) || 0;
                if (lastCount >= 0 && count > lastCount) {
                    showToast('New Notification', 'You have a new notification', null);
                }
                lastCount = count;
            }
        });
    }

    // Also try to hook into Echo/Pusher if available
    function setupEcho() {
        if (!window.Echo) return;

        // Get current user ID from meta tag or DOM
        const userIdMeta = document.querySelector('meta[name="user-id"]');
        const userId = userIdMeta ? userIdMeta.content : null;

        if (userId) {
            window.Echo.private('App.Models.User.' + userId)
                .listen('.database-notifications.sent', (e) => {
                    showToast('New Notification', 'You have a new notification', null);
                    // Trigger Filament to refresh notifications
                    if (window.Livewire) {
                        Livewire.emit('notificationsSent');
                    }
                });
        }
    }

    // Try to setup Echo after it loads
    setTimeout(setupEcho, 2000);
    setTimeout(setupEcho, 5000);

    // Expose globally for manual triggering
    window.showToast = showToast;
})();
