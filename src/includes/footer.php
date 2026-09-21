<!-- COOKIE BANNER -->
<div class="cookie-banner" id="cookieBanner">
    <p class="cookie-text">
        Nous utilisons des cookies pour améliorer votre expérience.
        <a href="/pages/mentions-legales.php">En savoir plus</a>
    </p>
    <div style="display: flex; gap: 10px; flex-shrink: 0;">
        <button class="btn-cookie-refuser" onclick="refuseCookies()">Refuser</button>
        <button class="btn-cookie" onclick="acceptCookies()">Accepter</button>
    </div>
</div>

<!-- NEWSLETTER TEASER -->
<section class="newsletter-section">
    <div class="newsletter-inner">
        <div class="newsletter-icon">✉</div>
        <h3 class="newsletter-title">Ne manquez aucun lot</h3>
        <p class="newsletter-sub">Recevez nos actualités et les prochains jeux-concours par email.</p>
        <a href="/pages/newsletter.php" class="newsletter-btn" style="text-decoration:none; display:inline-block;">S'inscrire</a>
    </div>
</section>

<!-- FOOTER -->
<footer class="ttt-footer">
    <div class="footer-inner">
        <div>
            <div class="footer-logo">
                <span style="color: var(--or);">☽</span> Thé Tip Top
            </div>
            <p class="footer-tagline">
                Thés bio et handmade d'exception.<br>
                Qualité premium, jeu-concours 100% gagnant.
            </p>
            <div style="margin-top: 1.2rem; display: flex; gap: 14px; align-items: center;">
                <a href="https://www.instagram.com/thetiptopoffi/" target="_blank" rel="noopener"
                   aria-label="Instagram Thé Tip Top" class="social-icon">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect x="2" y="2" width="20" height="20" rx="5" stroke="#F5EDD6" stroke-width="1.8"/>
                        <circle cx="12" cy="12" r="4.2" stroke="#F5EDD6" stroke-width="1.8"/>
                        <circle cx="17.3" cy="6.7" r="1.1" fill="#F5EDD6"/>
                    </svg>
                </a>
                <a href="https://www.facebook.com/share/1DPCFmBXrv/?mibextid=wwXIfr" target="_blank" rel="noopener"
                   aria-label="Facebook Thé Tip Top" class="social-icon">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M15 8.5H17V5.5H15C13.067 5.5 11.5 7.067 11.5 9V11H9.5V14H11.5V19.5H14.5V14H16.5L17 11H14.5V9C14.5 8.724 14.724 8.5 15 8.5Z" fill="#F5EDD6"/>
                    </svg>
                </a>
            </div>
        </div>
        <div>
            <div class="footer-heading">Navigation</div>
            <ul class="footer-nav">
                <li><a href="/">Accueil</a></li>
                <?php if (!isset($is_logged) || !$is_logged || (isset($user) && $user && $user['role'] === 'client')): ?>
                    <li><a href="/pages/participation.php">Participer</a></li>
                <?php endif; ?>
                <?php if (isset($is_logged) && $is_logged && isset($user) && $user): ?>
                    <?php if ($user['role'] === 'admin'): ?>
                        <li><a href="/pages/admin.php">Administration</a></li>
                    <?php elseif ($user['role'] === 'employe'): ?>
                        <li><a href="/pages/employe.php">Espace boutique</a></li>
                    <?php else: ?>
                        <li><a href="/pages/mon-compte.php">Mon compte</a></li>
                    <?php endif; ?>
                <?php endif; ?>
                <li><a href="/pages/reglement.php">Règlement</a></li>
                <li><a href="/pages/mentions-legales.php">Mentions légales</a></li>
                <li><a href="/pages/confidentialite.php">Confidentialité</a></li>
                <li><a href="/pages/newsletter.php">Newsletter</a></li>
            </ul>
        </div>
        <div>
            <div class="footer-heading">Notre histoire</div>
            <p class="footer-tagline">
                Une sélection de thés bio et handmade, préparés avec exigence pour un rituel sensoriel unique.
            </p>
            <p class="footer-tagline" style="margin-top: 0.8rem; font-size: 0.72rem;">
                Règlement déposé chez Maître Arnaud Rick, huissier de justice.
            </p>
        </div>
    </div>
    <div class="footer-bottom">
        <span class="footer-copy">
            &copy; <?= date('Y') ?> Thé Tip Top — Tous droits réservés |
            Réalisé par <strong>G-TECH</strong> (Groupe 6 — DSP5 ARCHI O24A — Agence Furious Ducks)
        </span>
        <span class="footer-copy">
            Nice, France · <a href="#" onclick="manageCookies(); return false;" style="color:inherit; text-decoration:underline; opacity:0.7;">Gérer les cookies</a>
        </span>
    </div>
</footer>

<script src="/assets/js/main.js"></script>
</body>
</html>