<?php
session_start();
$page_title = 'Thé Tip Top — Jeu-Concours 100% Gagnant | Thés du Sahara Marocain';
require_once __DIR__ . '/includes/header.php';
?>

<!-- ===================== HERO ===================== -->
<section class="ttt-hero">
    <div class="hero-bg-gradient"></div>
    <div class="hero-grid-pattern"></div>

    <!-- Visuel droit -->
    <div class="hero-visual">
        <div class="hero-img-wrapper">
            <div class="hero-circle hc1"></div>
            <div class="hero-circle hc2"></div>
            <div class="hero-circle hc3"></div>
            <img
                src="https://images.unsplash.com/photo-1556679343-c7306c1976bc?w=400&q=85&auto=format"
                alt="Thé marocain artisanal"
                class="hero-photo"
                loading="eager"
            >
            <div class="steam-wrap">
                <div class="steam"></div>
                <div class="steam"></div>
                <div class="steam"></div>
            </div>
        </div>
    </div>

    <!-- Contenu gauche -->
    <div class="hero-content">
        <div class="hero-eyebrow">Ouverture 10e boutique — Nice</div>

        <h1 class="hero-title">
            Le jeu-concours<br>
            <em>100% gagnant</em><br>
            du Sahara
        </h1>

        <p class="hero-desc">
            Chaque achat supérieur à 49€ vous offre un code unique.
            Découvrez votre lot et vivez l'expérience Thé Tip Top —
            thés bio et handmade du cœur du Maroc.
        </p>

        <div class="hero-btns">
            <a href="/pages/participation.php" class="btn-primary-ttt">Entrer mon code</a>
            <a href="/pages/inscription.php" class="btn-ghost-ttt">S'inscrire gratuitement</a>
        </div>

        <div class="hero-stats">
            <div>
                <div class="stat-num">500K</div>
                <div class="stat-lbl">codes gagnants</div>
            </div>
            <div>
                <div class="stat-num">100%</div>
                <div class="stat-lbl">gagnant garanti</div>
            </div>
            <div>
                <div class="stat-num">30j</div>
                <div class="stat-lbl">pour participer</div>
            </div>
        </div>
    </div>

    <div class="hero-scroll-hint">
        <div class="line"></div>
        <span>Découvrir</span>
    </div>
</section>

<!-- ===================== BANDEAU ===================== -->
<div class="ttt-bandeau" aria-hidden="true">
    <div class="bandeau-track">
        <span class="bandeau-item">🌿 100% Bio <span class="bandeau-sep">·</span></span>
        <span class="bandeau-item">✋ Handmade <span class="bandeau-sep">·</span></span>
        <span class="bandeau-item">♻️ Éco-responsable <span class="bandeau-sep">·</span></span>
        <span class="bandeau-item">🌍 Origine Maroc <span class="bandeau-sep">·</span></span>
        <span class="bandeau-item">☕ Tradition berbère <span class="bandeau-sep">·</span></span>
        <span class="bandeau-item">🏆 500 000 codes gagnants <span class="bandeau-sep">·</span></span>
        <span class="bandeau-item">🌿 100% Bio <span class="bandeau-sep">·</span></span>
        <span class="bandeau-item">✋ Handmade <span class="bandeau-sep">·</span></span>
        <span class="bandeau-item">♻️ Éco-responsable <span class="bandeau-sep">·</span></span>
        <span class="bandeau-item">🌍 Origine Maroc <span class="bandeau-sep">·</span></span>
        <span class="bandeau-item">☕ Tradition berbère <span class="bandeau-sep">·</span></span>
        <span class="bandeau-item">🏆 500 000 codes gagnants <span class="bandeau-sep">·</span></span>
    </div>
</div>

<!-- ===================== NOTRE HISTOIRE ===================== -->
<section class="ttt-histoire reveal">
    <div class="histoire-visuel">
        <img
            src="https://images.unsplash.com/photo-1593491205049-7f032d28cf01?w=800&q=85&auto=format"
            alt="Médina marocaine, berceau des thés Tip Top"
            loading="lazy"
        >
        <div class="histoire-visuel-overlay">
            <div class="histoire-badge-mini">
                <div class="badge-titre">Né au cœur du Sahara</div>
                <div class="badge-sub">Tradition depuis des siècles</div>
            </div>
        </div>
    </div>
    <div class="histoire-texte">
        <div class="section-eyebrow">Notre histoire</div>
        <h2 class="section-title">
            Des médinas de Marrakech<br>à <em>Nice</em>
        </h2>
        <p class="section-body">
            L'histoire de Thé Tip Top commence dans les médinas dorées de Marrakech et de Fès,
            où les souffles chauds du désert portent le parfum des feuilles séchées au soleil.
            Inspirés par les traditions ancestrales des Touaregs et des Berbères, nos artisans
            préparent chaque mélange à la main, selon des recettes transmises depuis des siècles.
        </p>
        <p class="section-body" style="margin-top: -0.8rem;">
            Des oasis verdoyantes de l'Atlas aux jardins secrets de l'Anti-Atlas, nous sélectionnons
            les feuilles les plus nobles, récoltées à l'aube pour préserver tous leurs arômes.
        </p>
        <div class="badges-row">
            <span class="badge-pill">🌍 Origine Maroc</span>
            <span class="badge-pill">🌿 Bio certifié</span>
            <span class="badge-pill">✋ Artisanal</span>
            <span class="badge-pill">♻️ RSE</span>
        </div>
    </div>
</section>

<!-- ===================== COMMENT PARTICIPER ===================== -->
<section class="ttt-participer reveal">
    <div class="section-eyebrow" style="justify-content:center; margin-bottom: 0.8rem;">Comment participer</div>
    <h2 class="section-title-light">
        Quatre étapes,<br><em>100% gagnant</em>
    </h2>
    <p class="section-sub-light">Simple, rapide, garanti — votre lot vous attend</p>

    <div class="steps-row">
        <div class="step-card">
            <span class="step-big-num">01</span>
            <span class="step-icon-em">🛍️</span>
            <div class="step-label">Achetez</div>
            <p class="step-desc">Effectuez un achat de plus de 49€ dans notre boutique de Nice.</p>
        </div>
        <div class="step-card">
            <span class="step-big-num">02</span>
            <span class="step-icon-em">🎟️</span>
            <div class="step-label">Récupérez votre code</div>
            <p class="step-desc">Trouvez votre code unique à 10 caractères sur votre ticket de caisse.</p>
        </div>
        <div class="step-card">
            <span class="step-big-num">03</span>
            <span class="step-icon-em">🖥️</span>
            <div class="step-label">Saisissez en ligne</div>
            <p class="step-desc">Connectez-vous et entrez votre code pour découvrir votre gain immédiatement.</p>
        </div>
        <div class="step-card">
            <span class="step-big-num">04</span>
            <span class="step-icon-em">🎁</span>
            <div class="step-label">Réclamez votre lot</div>
            <p class="step-desc">Réclamez votre gain en magasin ou en ligne sous 30 jours.</p>
        </div>
    </div>

    <a href="/pages/participation.php" class="btn-primary-ttt">Entrer mon code maintenant</a>
</section>

<!-- ===================== LES LOTS ===================== -->
<section class="ttt-lots reveal">
    <div class="lots-header">
        <div class="section-eyebrow" style="justify-content:center; margin-bottom: 0.8rem;">
            500 000 codes — 100% gagnants
        </div>
        <h2 class="section-title">Les lots à gagner</h2>
    </div>

    <div class="lots-grid">
        <div class="lot-card">
            <span class="lot-icon-em">🍵</span>
            <div class="lot-pct">60%</div>
            <div class="lot-name">Infuseur à thé</div>
            <p class="lot-desc">Un infuseur premium pour préparer votre thé à la perfection.</p>
        </div>
        <div class="lot-card">
            <span class="lot-icon-em">🌿</span>
            <div class="lot-pct">20%</div>
            <div class="lot-name">Thé détox 100g</div>
            <p class="lot-desc">Une boîte de 100g d'un thé détox ou d'infusion bio du Maroc.</p>
        </div>
        <div class="lot-card">
            <span class="lot-icon-em">⭐</span>
            <div class="lot-pct">10%</div>
            <div class="lot-name">Thé signature 100g</div>
            <p class="lot-desc">Un de nos mélanges signatures exclusifs, handmade.</p>
        </div>
        <div class="lot-card">
            <span class="lot-icon-em">🎁</span>
            <div class="lot-pct">6%</div>
            <div class="lot-name">Coffret 39€</div>
            <p class="lot-desc">Un coffret découverte de nos thés d'exception.</p>
        </div>
        <div class="lot-card">
            <span class="lot-icon-em">✨</span>
            <div class="lot-pct">4%</div>
            <div class="lot-name">Coffret 69€</div>
            <p class="lot-desc">Notre coffret premium — une expérience sensorielle complète.</p>
        </div>
        <div class="lot-card">
            <span class="lot-icon-em">🎲</span>
            <div class="lot-pct">–</div>
            <div class="lot-name">Tirage au sort</div>
            <p class="lot-desc">Tirage parmi tous les participants pour des lots supplémentaires.</p>
        </div>

        <!-- GRAND PRIX -->
        <div class="lot-card grand-prix">
            <div>
                <div class="grand-prix-badge">✦ Grand Prix</div>
                <div class="lot-pct">1 an</div>
            </div>
            <div>
                <div class="lot-name">Un an de thé offert</div>
                <p class="lot-desc">
                    Valeur 360€ — Tirage au sort parmi tous les participants.
                    Le lot ultime pour les vrais amateurs de thé bio du Sahara marocain.
                </p>
            </div>
        </div>
    </div>

    <div style="text-align:center; margin-top:2.5rem;">
        <a href="/pages/participation.php" class="btn-primary-ttt">Participer maintenant</a>
    </div>
</section>

<!-- ===================== 10e BOUTIQUE NICE ===================== -->
<section class="ttt-boutique reveal">
    <div class="boutique-texte">
        <div class="section-eyebrow">Notre 10e boutique</div>
        <h2 class="section-title" style="color: var(--creme);">
            La magie du Maroc<br>à <em style="color: var(--or-clair);">Nice</em>
        </h2>
        <ul class="boutique-list">
            <li>Mélanges signatures exclusifs du Maroc</li>
            <li>100% Bio — certifié par des organismes indépendants</li>
            <li>Handmade — préparé à la main par nos artisans</li>
            <li>Démarche RSE et éco-responsable</li>
        </ul>
        <div class="boutique-mini-stats">
            <div>
                <div class="b-stat-num">30j</div>
                <div class="b-stat-lbl">de jeu</div>
            </div>
            <div>
                <div class="b-stat-num">100%</div>
                <div class="b-stat-lbl">gagnant</div>
            </div>
            <div>
                <div class="b-stat-num">500K</div>
                <div class="b-stat-lbl">codes</div>
            </div>
        </div>
    </div>
    <div class="boutique-visuel">
        <img
            src="https://images.unsplash.com/photo-1540189549336-e6e99c3679fe?w=800&q=85&auto=format"
            alt="Boutique Thé Tip Top Nice"
            loading="lazy"
        >
        <div class="boutique-visuel-overlay"></div>
    </div>
</section>

<!-- ===================== CTA FINAL ===================== -->
<section class="ttt-cta reveal">
    <h2 class="cta-title">
        Votre code<br><em>infuse la chance</em>
    </h2>
    <p class="cta-sub">10e boutique Nice · 30 jours · 500 000 gagnants</p>
    <div class="cta-btns">
        <a href="/pages/participation.php" class="btn-primary-ttt">Entrer mon code</a>
        <a href="/pages/inscription.php" class="btn-ghost-ttt">Créer un compte</a>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
