<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$page_title = 'Règlement du Jeu — Thé Tip Top';
require_once __DIR__ . '/../includes/header.php';
?>

<style>
.legal-page {
    background: var(--blanc-casse);
    min-height: calc(100vh - 68px);
    padding: 4rem 1.5rem;
}
.legal-container {
    max-width: 780px;
    margin: 0 auto;
}
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
    line-height: 1.15;
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
.legal-article:hover {
    box-shadow: 0 4px 20px rgba(26,46,26,0.06);
}
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
.legal-gains-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 8px;
    margin-top: 0.8rem;
}
.legal-gain-item {
    background: var(--blanc-casse);
    border: 1px solid rgba(45,74,45,0.1);
    border-radius: 2px;
    padding: 10px 12px;
    text-align: center;
    font-size: 0.78rem;
}
.legal-gain-pct {
    font-family: 'Playfair Display', serif;
    font-size: 1.2rem;
    font-weight: 700;
    color: var(--or);
    display: block;
}
.legal-gain-label {
    color: #6a7f6a;
    font-size: 0.72rem;
    margin-top: 2px;
}
.legal-highlight {
    background: var(--vert-nuit);
    border-radius: 4px;
    padding: 1.5rem 2rem;
    margin-top: 1.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
}
.legal-highlight-icon { font-size: 2rem; }
.legal-highlight-text {
    font-size: 0.88rem;
    color: rgba(245,237,214,0.8);
    line-height: 1.6;
}
.legal-highlight-text strong { color: var(--or); }
</style>

<div class="legal-page">
    <div class="legal-container">
        <div class="legal-eyebrow">Jeu-Concours</div>
        <h1 class="legal-title">Règlement du Jeu-Concours</h1>

        <div class="legal-article">
            <div class="legal-article-title">Article 1 — Organisateur</div>
            <div class="legal-article-body">
                Thé Tip Top, SA au capital de 150 000€, dont le siège social est situé au 18 rue Léon Frot, 75011 Paris.
            </div>
        </div>

        <div class="legal-article">
            <div class="legal-article-title">Article 2 — Participation</div>
            <div class="legal-article-body">
                Tout achat supérieur à 49€ donne droit à un code unique à 10 caractères. Un client peut utiliser plusieurs codes. La participation est ouverte aux personnes majeures résidant en France.
            </div>
        </div>

        <div class="legal-article">
            <div class="legal-article-title">Article 3 — Durée</div>
            <div class="legal-article-body">
                Le jeu se déroule sur 30 jours. Les participants disposent de 30 jours supplémentaires après la clôture pour valider leur code et réclamer leur lot.
            </div>
        </div>

        <div class="legal-article">
            <div class="legal-article-title">Article 4 — Gains</div>
            <div class="legal-article-body">
                100% des codes sont gagnants selon la répartition suivante :
                <div class="legal-gains-grid">
                    <div class="legal-gain-item">
                        <span class="legal-gain-pct">60%</span>
                        <div class="legal-gain-label">🍵 Infuseur à thé</div>
                    </div>
                    <div class="legal-gain-item">
                        <span class="legal-gain-pct">20%</span>
                        <div class="legal-gain-label">🌿 Thé détox 100g</div>
                    </div>
                    <div class="legal-gain-item">
                        <span class="legal-gain-pct">10%</span>
                        <div class="legal-gain-label">⭐ Thé signature 100g</div>
                    </div>
                    <div class="legal-gain-item">
                        <span class="legal-gain-pct">6%</span>
                        <div class="legal-gain-label">🎁 Coffret 39€</div>
                    </div>
                    <div class="legal-gain-item">
                        <span class="legal-gain-pct">4%</span>
                        <div class="legal-gain-label">✨ Coffret 69€</div>
                    </div>
                    <div class="legal-gain-item">
                        <span class="legal-gain-pct">–</span>
                        <div class="legal-gain-label">🎲 Tirage au sort</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="legal-article">
            <div class="legal-article-title">Article 5 — Grand Prix</div>
            <div class="legal-article-body">
                Un tirage au sort sera effectué parmi tous les participants sous le contrôle de Maître Arnaud Rick, huissier de justice. Le gagnant recevra 1 an de thé d'une valeur de 360€. Le nombre de participations n'augmente pas les chances de gagner au grand prix.
            </div>
        </div>

        <div class="legal-article">
            <div class="legal-article-title">Article 6 — Réclamation des lots</div>
            <div class="legal-article-body">
                Les lots peuvent être réclamés en boutique ou en ligne dans les délais impartis. Passé ce délai, le lot est définitivement perdu.
            </div>
        </div>

        <div class="legal-highlight">
            <div class="legal-highlight-icon">⚖️</div>
            <div class="legal-highlight-text">
                Règlement déposé auprès de <strong>Maître Arnaud Rick</strong>, huissier de justice.
                Le tirage au sort final sera effectué sous son contrôle.
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>