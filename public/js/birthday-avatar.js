/**
 * Add floating balloon animation around nav avatar when any birthday today
 */
(function() {
    const selfBday = document.querySelector('meta[name="user-birthday-today"]');
    const anyBday = document.querySelector('meta[name="any-birthday-today"]');
    if ((!selfBday || selfBday.content !== '1') && (!anyBday || anyBday.content !== '1')) return;

    const colors = ['#f87171','#fb923c','#facc15','#4ade80','#60a5fa','#a78bfa','#f472b6'];

    function addBalloons() {
        // Find nav avatar: look for rounded-full img inside the topbar/header area
        let avatarImg = null;

        // Try multiple selectors
        const candidates = document.querySelectorAll('img.rounded-full, img[class*="rounded-full"]');
        for (const img of candidates) {
            // Must be in the top navigation area (small avatar, usually < 50px)
            const rect = img.getBoundingClientRect();
            if (rect.top < 80 && rect.width < 50 && rect.width > 20) {
                avatarImg = img;
                break;
            }
        }

        if (!avatarImg) return;

        // Find a suitable parent to attach balloons
        const parent = avatarImg.closest('button') || avatarImg.closest('a') || avatarImg.parentElement;
        if (!parent || parent.dataset.balloonsAdded) return;

        parent.style.position = 'relative';
        parent.style.overflow = 'visible';
        parent.dataset.balloonsAdded = 'true';

        // Inject animation style
        if (!document.getElementById('nav-balloon-style')) {
            const style = document.createElement('style');
            style.id = 'nav-balloon-style';
            style.textContent = `
                @keyframes navBalloonFloat {
                    0% { transform: translateY(0) scale(0.2); opacity: 0; }
                    8% { opacity: 1; transform: translateY(-3px) scale(1); }
                    70% { opacity: 1; }
                    100% { transform: translateY(-45px) translateX(var(--drift)) scale(0.5); opacity: 0; }
                }
            `;
            document.head.appendChild(style);
        }

        // Create 6 balloons
        for (let i = 0; i < 6; i++) {
            const balloon = document.createElement('div');
            const color = colors[i % colors.length];
            const size = 4 + Math.random() * 3;
            const left = -4 + Math.random() * (parent.offsetWidth + 4);
            const delay = i * 0.5;
            const dur = 2.2 + Math.random() * 1.3;
            const drift = -8 + Math.random() * 16;

            balloon.style.cssText = `
                position:absolute;
                width:${size}px;
                height:${size * 1.2}px;
                background:${color};
                border-radius:50% 50% 50% 50% / 40% 40% 60% 60%;
                left:${left}px;
                bottom:0;
                pointer-events:none;
                z-index:999;
                opacity:0;
                animation:navBalloonFloat ${dur}s ${delay}s ease-in-out infinite;
                --drift:${drift}px;
            `;
            parent.appendChild(balloon);
        }

        console.log('Birthday balloons added to nav avatar');
    }

    // Retry since Filament nav loads async with Livewire
    setTimeout(addBalloons, 800);
    setTimeout(addBalloons, 2000);
    setTimeout(addBalloons, 4000);
    setTimeout(addBalloons, 6000);
    document.addEventListener('livewire:load', () => setTimeout(addBalloons, 1000));
    document.addEventListener('livewire:update', () => setTimeout(addBalloons, 500));
})();
