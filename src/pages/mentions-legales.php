<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$page_title = 'Mentions Légales — Thé Tip Top';
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
</style>

<div class="legal-page">
    <div class="legal-container">
        <div class="legal-eyebrow">Informations légales</div>
        <h1 class="legal-title">Mentions Légales</h1>

        <div class="legal-article">
            <div class="legal-article-title">Éditeur du site</div>
            <div class="legal-article-body">
                Thé Tip Top — SA au capital de 150 000€<br>
                Siège social : 18 rue Léon Frot, 75011 Paris<br>
                Gérant : M. Eric Bourdon<br>
                Email : <a href="mailto:contact@thetiptop.fr" style="color:var(--or);">contact@thetiptop.fr</a>
            </div>
        </div>

        <div class="legal-article">
            <div class="legal-article-title">Réalisation</div>
            <div class="legal-article-body">
                Site réalisé par <strong>G-TECH</strong> (Groupe 6 — DSP5 ARCHI O24A)<br>
                Agence Furious Ducks — 10 rue des Lilas, Créteil 94000
            </div>
        </div>

        <div class="legal-article">
            <div class="legal-article-title">Hébergement</div>
            <div class="legal-article-body">
                Ce site est hébergé par <strong>Render.com</strong> sur un serveur sécurisé sous distribution Linux.
            </div>
        </div>

        <div class="legal-article">
            <div class="legal-article-title">Règlement du jeu-concours</div>
            <div class="legal-article-body">
                Le règlement du jeu-concours a été déposé auprès de Maître Arnaud Rick, huissier de justice. Le tirage au sort final sera effectué sous son contrôle.
            </div>
        </div>

        <div class="legal-article">
            <div class="legal-article-title">Propriété intellectuelle</div>
            <div class="legal-article-body">
                L'ensemble des contenus de ce site (textes, images, logos) sont la propriété de Thé Tip Top et sont protégés par le droit d'auteur. Toute reproduction est interdite sans autorisation préalable.
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>