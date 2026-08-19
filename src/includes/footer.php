<!-- COOKIE BANNER -->
<div class="cookie-banner" id="cookieBanner">
    <p class="cookie-text">
        Nous utilisons des cookies pour améliorer votre expérience.
        <a href="/pages/mentions-legales.php">En savoir plus</a>
    </p>
    <button class="btn-cookie" onclick="acceptCookies()">Accepter</button>
</div>

<!-- FOOTER -->
<footer class="ttt-footer">
    <div class="footer-inner">
        <div>
            <div class="footer-logo">
                <span style="color: var(--or);">☽</span> Thé Tip Top
            </div>
            <p class="footer-tagline">
                Thés bio et handmade du Sahara marocain.<br>
                Tradition berbère, qualité premium, jeu-concours 100% gagnant.
            </p>
            <div style="margin-top: 1.2rem; display: flex; gap: 12px;">
                <a href="https://instagram.com/thetiptop_officiel" target="_blank" rel="noopener"
                   style="font-size: 0.72rem; color: rgba(245,237,214,0.35); text-decoration: none; transition: color 0.2s;"
                   onmouseover="this.style.color='var(--or)'" onmouseout="this.style.color='rgba(245,237,214,0.35)'">
                    Instagram
                </a>
                <span style="color: rgba(245,237,214,0.15);">·</span>
                <a href="https://facebook.com/thetiptop" target="_blank" rel="noopener"
                   style="font-size: 0.72rem; color: rgba(245,237,214,0.35); text-decoration: none; transition: color 0.2s;"
                   onmouseover="this.style.color='var(--or)'" onmouseout="this.style.color='rgba(245,237,214,0.35)'">
                    Facebook
                </a>
            </div>
        </div>
        <div>
            <div class="footer-heading">Navigation</div>
            <ul class="footer-nav">
                <li><a href="/">Accueil</a></li>
                <li><a href="/pages/participation.php">Participer</a></li>
                <li><a href="/pages/reglement.php">Règlement</a></li>
                <li><a href="/pages/mentions-legales.php">Mentions légales</a></li>
                <li><a href="/pages/confidentialite.php">Confidentialité</a></li>
            </ul>
        </div>
        <div>
            <div class="footer-heading">Notre histoire</div>
            <p class="footer-tagline">
                Nés au cœur des médinas de Marrakech et Fès, nos thés racontent l'histoire des peuples du désert.
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
        <span class="footer-copy">Nice, France</span>
    </div>
</footer>

<script src="/assets/js/main.js"></script>
</body>
</html>
