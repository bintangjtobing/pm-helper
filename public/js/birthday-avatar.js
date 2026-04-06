/**
 * Add floating balloon animation around nav avatar when any birthday today
 * Filament v2: avatar is a DIV with background-image, NOT an img tag
 */
(function() {
    var selfBday = document.querySelector('meta[name="user-birthday-today"]');
    var anyBday = document.querySelector('meta[name="any-birthday-today"]');
    if ((!selfBday || selfBday.content !== '1') && (!anyBday || anyBday.content !== '1')) return;

    var colors = ['#f87171','#fb923c','#facc15','#4ade80','#60a5fa','#a78bfa','#f472b6'];

    function addBalloons() {
        // Filament v2 user avatar: div.rounded-full with background-image in topbar
        var avatarDiv = null;
        var allDivs = document.querySelectorAll('div.rounded-full');

        for (var d = 0; d < allDivs.length; d++) {
            var div = allDivs[d];
            var st = div.getAttribute('style') || '';
            var rect = div.getBoundingClientRect();
            if (st.indexOf('background-image') !== -1 && rect.top < 80 && rect.width >= 28 && rect.width <= 50) {
                avatarDiv = div;
                break;
            }
        }

        if (!avatarDiv) return;

        var parent = avatarDiv.closest('button') || avatarDiv.parentElement;
        if (!parent || parent.dataset.balloonsAdded) return;

        parent.style.position = 'relative';
        parent.style.overflow = 'visible';
        parent.dataset.balloonsAdded = 'true';

        // Inject animation style
        if (!document.getElementById('nav-balloon-style')) {
            var style = document.createElement('style');
            style.id = 'nav-balloon-style';
            style.textContent = '@keyframes navBalloonFloat{0%{transform:translateY(0) scale(0.2);opacity:0}8%{opacity:1;transform:translateY(-3px) scale(1)}70%{opacity:1}100%{transform:translateY(-45px) translateX(var(--drift)) scale(0.5);opacity:0}}';
            document.head.appendChild(style);
        }

        var pw = parent.offsetWidth || 40;
        for (var i = 0; i < 6; i++) {
            var balloon = document.createElement('div');
            var color = colors[i % colors.length];
            var size = 4 + Math.random() * 3;
            var left = -4 + Math.random() * (pw + 4);
            var delay = i * 0.5;
            var dur = 2.2 + Math.random() * 1.3;
            var drift = -8 + Math.random() * 16;

            balloon.style.cssText = 'position:absolute;width:' + size + 'px;height:' + (size * 1.2) + 'px;background:' + color + ';border-radius:50% 50% 50% 50% / 40% 40% 60% 60%;left:' + left + 'px;bottom:0;pointer-events:none;z-index:999;opacity:0;animation:navBalloonFloat ' + dur + 's ' + delay + 's ease-in-out infinite;--drift:' + drift + 'px;';
            parent.appendChild(balloon);
        }
    }

    // Retry multiple times for async Livewire load
    var delays = [500, 1500, 3000, 5000, 8000];
    for (var t = 0; t < delays.length; t++) {
        setTimeout(addBalloons, delays[t]);
    }
})();
