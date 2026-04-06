/**
 * Add floating balloon animation around nav avatar if user has birthday today
 */
(function() {
    const meta = document.querySelector('meta[name="user-birthday-today"]');
    if (!meta || meta.content !== '1') return;

    const colors = ['#f87171','#fb923c','#facc15','#4ade80','#60a5fa','#a78bfa','#f472b6'];

    function addBalloons() {
        // Find the nav avatar (button with avatar image in top bar)
        const avatarBtn = document.querySelector('.filament-user-avatar, [class*="shrink-0"] > button > img[class*="rounded-full"], header img[class*="rounded-full"]');
        if (!avatarBtn) return;

        const parent = avatarBtn.closest('button') || avatarBtn.parentElement;
        if (!parent || parent.dataset.balloonsAdded) return;

        parent.style.position = 'relative';
        parent.style.overflow = 'visible';
        parent.dataset.balloonsAdded = 'true';

        // Add style if not exists
        if (!document.getElementById('nav-balloon-style')) {
            const style = document.createElement('style');
            style.id = 'nav-balloon-style';
            style.textContent = `
                @keyframes navBalloonFloat {
                    0% { transform: translateY(0) scale(0.2); opacity: 0; }
                    8% { opacity: 1; transform: translateY(-3px) scale(1); }
                    70% { opacity: 1; }
                    100% { transform: translateY(-50px) translateX(var(--drift)) scale(0.5); opacity: 0; }
                }
                .nav-balloon {
                    position: absolute;
                    border-radius: 50% 50% 50% 50% / 40% 40% 60% 60%;
                    pointer-events: none;
                    z-index: 999;
                    animation: navBalloonFloat var(--dur) var(--delay) ease-in-out infinite;
                    opacity: 0;
                }
                .nav-balloon::after {
                    content: '';
                    position: absolute;
                    bottom: -3px;
                    left: 50%;
                    width: 1px;
                    height: 3px;
                    background: inherit;
                    opacity: 0.5;
                    transform: translateX(-50%);
                }
            `;
            document.head.appendChild(style);
        }

        // Create 6 balloons
        for (let i = 0; i < 6; i++) {
            const balloon = document.createElement('div');
            balloon.className = 'nav-balloon';
            const color = colors[i % colors.length];
            const size = 4 + Math.random() * 3;
            const left = -2 + Math.random() * 30;
            const delay = i * 0.6;
            const dur = 2.5 + Math.random() * 1.5;
            const drift = -6 + Math.random() * 12;

            balloon.style.cssText = `
                width:${size}px;
                height:${size * 1.2}px;
                background:${color};
                left:${left}px;
                bottom:0;
                --delay:${delay}s;
                --dur:${dur}s;
                --drift:${drift}px;
            `;
            parent.appendChild(balloon);
        }
    }

    // Try multiple times since nav loads async
    setTimeout(addBalloons, 500);
    setTimeout(addBalloons, 1500);
    setTimeout(addBalloons, 3000);
    document.addEventListener('livewire:load', () => setTimeout(addBalloons, 500));
})();
