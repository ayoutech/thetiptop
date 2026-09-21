// ======= COOKIE BANNER =======
document.addEventListener('DOMContentLoaded', function () {

    const banner = document.getElementById('cookieBanner');
    if (banner && !localStorage.getItem('ttt_cookies_choice')) {
        banner.classList.add('visible');
    }

    // ======= NAV STICKY SHADOW =======
    const nav = document.getElementById('mainNav');
    window.addEventListener('scroll', function () {
        if (window.scrollY > 20) {
            nav && nav.classList.add('scrolled');
        } else {
            nav && nav.classList.remove('scrolled');
        }
    }, { passive: true });

    // ======= HAMBURGER MENU MOBILE =======
    const hamburger = document.getElementById('hamburger');
    const mobileMenu = document.getElementById('mobileMenu');
    if (hamburger && mobileMenu) {
        hamburger.addEventListener('click', function () {
            const isOpen = mobileMenu.style.display === 'flex';
            mobileMenu.style.display = isOpen ? 'none' : 'flex';
        });
    }

    // ======= SCROLL REVEAL =======
    const reveals = document.querySelectorAll('.reveal');
    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry, i) {
                if (entry.isIntersecting) {
                    setTimeout(function () {
                        entry.target.classList.add('visible');
                    }, i * 60);
                }
            });
        }, { threshold: 0.08 });
        reveals.forEach(function (el) { observer.observe(el); });
    } else {
        reveals.forEach(function (el) { el.classList.add('visible'); });
    }

    // ======= CODE INPUT (page participation) =======
    const codeInput = document.getElementById('code-input');
    if (codeInput) {
        codeInput.addEventListener('input', function () {
            this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
            if (this.value.length > 10) this.value = this.value.slice(0, 10);
        });
    }

});

// ======= COOKIES : ACCEPTER =======
function acceptCookies() {
    localStorage.setItem('ttt_cookies_choice', 'accepted');
    hideCookieBanner();
}

// ======= COOKIES : REFUSER =======
function refuseCookies() {
    localStorage.setItem('ttt_cookies_choice', 'refused');
    hideCookieBanner();
}

// ======= COOKIES : MASQUER LA BANNIÈRE =======
function hideCookieBanner() {
    const banner = document.getElementById('cookieBanner');
    if (banner) {
        banner.style.transition = 'opacity 0.4s';
        banner.style.opacity = '0';
        setTimeout(function () { banner.remove(); }, 400);
    }
}

// ======= COOKIES : ROUVRIR LA GESTION (lien footer) =======
function manageCookies() {
    localStorage.removeItem('ttt_cookies_choice');
    let banner = document.getElementById('cookieBanner');
    if (!banner) {
        banner = document.createElement('div');
        banner.id = 'cookieBanner';
        banner.className = 'cookie-banner visible';
        banner.innerHTML =
            '<p class="cookie-text">Nous utilisons des cookies pour améliorer votre expérience. ' +
            '<a href="/pages/mentions-legales.php">En savoir plus</a></p>' +
            '<div style="display: flex; gap: 10px; flex-shrink: 0;">' +
            '<button class="btn-cookie-refuser" onclick="refuseCookies()">Refuser</button>' +
            '<button class="btn-cookie" onclick="acceptCookies()">Accepter</button>' +
            '</div>';
        document.body.appendChild(banner);
    } else {
        banner.style.display = 'flex';
        banner.style.opacity = '1';
        banner.classList.add('visible');
    }
}