<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$page_title = 'Politique de Confidentialité — Thé Tip Top';
require_once __DIR__ . '/../includes/header.php';
?>

<style>
.legal-page {
    background: var(--blanc-casse);
    min-height: calc(100vh - 68px);
    padding: 4rem 1.5rem;
}
.legal-container { max-width: 780px; margin: 0 auto; }
.legal-eyebrow {
    font-size: 0.68rem;
    font-weight: 600;
    letter-spacing: 0.16em;
    text-transform: uppercase;
    color: var(--or);
    margin-bottom: 0.8rem;
    display: flex;
    align-items: center;
    gap: 10px;
}
.legal-eyebrow::before {
    content: '';
    display: inline-block;
    width: 20px; height: 1px;
    background: var(--or);
}
.legal-title {
    font-family: 'Playfair Display', serif;
    font-size: 2.2rem;
    font-weight: 700;
    color: var(--vert-nuit);
    margin-bottom: 2.5rem;
}
.legal-article {
    background: #fff;
    border: 1px solid rgba(45,74,45,0.08);
    border-radius: 4px;
    padding: 1.8rem 2rem;
    margin-bottom: 1rem;
    position: relative;
    overflow: hidden;
    transition: box-shadow 0.2s;
}
.legal-article::before {
    content: '';
    position: absolute;
    left: 0; top: 0; bottom: 0;
    width: 3px;
    background: var(--or);
}
.legal-article:hover { box-shadow: 0 4px 20px rgba(26,46,26,0.06); }
.legal-article-title {
    font-family: 'Playfair Display', serif;
    font-size: 1rem;
    font-weight: 700;
    color: var(--vert-nuit);
    margin-bottom: 0.6rem;
}
.legal-article-body {
    font-size: 0.88rem;
    color: #4a5e4a;
    line-height: 1.75;
}
.legal-article-body ul {
    padding-left: 1.2rem;
    margin-top: 0.5rem;
}
.legal-article-body ul li { margin-bottom: 0.3rem; }
.rgpd-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(45,74,45,0.06);
    border: 1px solid rgba(45,74,45,0.12);
    padding: 8px 16px;
    border-radius: 2px;
    font-size: 0.78rem;
    color: var(--vert-profond);
    font-weight: 600;
    margin-bottom: 2rem;
}
</style>

<div class="legal-page">
    <div class="legal-container">
        <div class="legal-eyebrow">Protection des données</div>
        <h1 class="legal-title">Politique de Confidentialité</h1>
        <div class="rgpd-badge">🔒 Conforme au RGPD — Règlement Général sur la Protection des Données</div>

        <div class="legal-article">
            <div class="legal-article-title">Données collectées</div>
            <div class="legal-article-body">
                Lors de votre inscription, nous collectons les informations suivantes : nom, prénom, adresse email, âge, sexe. Ces données sont strictement nécessaires à la participation au jeu-concours.
            </div>
        </div>

        <div class="legal-article">
            <div class="legal-article-title">Utilisation des données</div>
            <div class="legal-article-body">
                Vos données personnelles sont utilisées pour :
                <ul>
                    <li>Gérer votre participation au jeu-concours</li>
                    <li>Vous envoyer des emails si vous avez consenti à la newsletter</li>
                    <li>Établir des statistiques anonymisées sur les participants</li>
                </ul>
                Vos données ne seront jamais vendues ni cédées à des tiers.
            </div>
        </div>

        <div class="legal-article">
            <div class="legal-article-title">Conservation des données</div>
            <div class="legal-article-body">
                Vos données sont conservées pendant la durée du jeu-concours et 3 ans après sa clôture, conformément aux obligations légales.
            </div>
        </div>

        <div class="legal-article">
            <div class="legal-article-title">Vos droits</div>
            <div class="legal-article-body">
                Conformément au RGPD, vous disposez des droits suivants :
                <ul>
                    <li>Droit d'accès à vos données personnelles</li>
                    <li>Droit de rectification des données inexactes</li>
                    <li>Droit à l'effacement (droit à l'oubli)</li>
                    <li>Droit à la portabilité de vos données</li>
                    <li>Droit d'opposition au traitement</li>
                </ul>
                Pour exercer ces droits, contactez-nous à :
                <a href="mailto:privacy@thetiptop.fr" style="color:var(--or);">privacy@thetiptop.fr</a>
            </div>
        </div>

        <div class="legal-article">
            <div class="legal-article-title">Cookies</div>
            <div class="legal-article-body">
                Ce site utilise des cookies techniques nécessaires à son fonctionnement (sessions PHP) et Google Analytics (ID : G-WW2TK99XK2) pour mesurer l'audience de façon anonyme. Vous pouvez refuser les cookies analytiques en cliquant sur "Refuser" dans la bannière cookies.
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>