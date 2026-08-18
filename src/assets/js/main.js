// Gestion des cookies RGPD
document.addEventListener('DOMContentLoaded', function() {
    if (!localStorage.getItem('cookies_accepted')) {
        document.getElementById('cookie-banner').style.display = 'block';
    }
});

function acceptCookies() {
    localStorage.setItem('cookies_accepted', 'true');
    document.getElementById('cookie-banner').style.display = 'none';
}

// Mise en majuscules automatique du code
const codeInput = document.getElementById('code-input');
if (codeInput) {
    codeInput.addEventListener('input', function() {
        this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
        if (this.value.length > 10) this.value = this.value.slice(0, 10);
    });
}

// Confirmation avant action sensible
function confirmerAction(message) {
    return confirm(message || 'Confirmer cette action ?');
}
