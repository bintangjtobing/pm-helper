/**
 * Auto-detect user timezone from browser and sync to server
 * Only sends if timezone not yet set or different from detected
 */
(function() {
    const detected = Intl.DateTimeFormat().resolvedOptions().timeZone;
    if (!detected) return;

    // Check if we already synced this timezone (avoid repeated requests)
    const stored = localStorage.getItem('pm_timezone');
    if (stored === detected) return;

    // Send to server
    fetch('/user/timezone', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({ timezone: detected }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.ok) {
            localStorage.setItem('pm_timezone', detected);
        }
    })
    .catch(() => {});
})();
