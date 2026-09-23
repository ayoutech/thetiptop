<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$page_title = 'Thé Tip Top — Jeu-Concours 100% Gagnant | Thés Bio Handmade';
require_once __DIR__ . '/includes/header.php';
?>
<!-- Test webhook new build -->

<!-- ===================== HERO ===================== -->
<section class="ttt-hero">
    <div class="hero-bg-gradient"></div>
    <div class="hero-grid-pattern"></div>

    <div class="hero-visual">
        <div class="hero-img-wrapper">
            <div class="hero-circle hc1"></div>
            <div class="hero-circle hc2"></div>
            <div class="hero-circle hc3"></div>
            <div class="hero-winner-badge">✨ 100% GAGNANT</div>

            <svg width="340" height="340" viewBox="0 0 340 340" xmlns="http://www.w3.org/2000/svg" role="img" aria-hidden="true" style="position:relative; z-index:2;">
              <defs>
                <radialGradient id="glowBurst" cx="50%" cy="38%" r="55%">
                  <stop offset="0%" stop-color="#E8C96A" stop-opacity="0.55"/>
                  <stop offset="45%" stop-color="#C9A84C" stop-opacity="0.22"/>
                  <stop offset="100%" stop-color="#C9A84C" stop-opacity="0"/>
                </radialGradient>
                <linearGradient id="lidShine" x1="0%" y1="0%" x2="100%" y2="100%">
                  <stop offset="0%" stop-color="#3D6B3D"/>
                  <stop offset="100%" stop-color="#2D4A2D"/>
                </linearGradient>
              </defs>
              <circle cx="170" cy="150" r="150" fill="url(#glowBurst)"/>
              <g stroke="#E8C96A" stroke-linecap="round">
                <line x1="170" y1="150" x2="120" y2="55" stroke-width="3" opacity="0.55"/>
                <line x1="170" y1="150" x2="170" y2="38" stroke-width="4" opacity="0.7"/>
                <line x1="170" y1="150" x2="222" y2="52" stroke-width="3" opacity="0.5"/>
                <line x1="170" y1="150" x2="95" y2="92" stroke-width="2.5" opacity="0.4"/>
                <line x1="170" y1="150" x2="248" y2="88" stroke-width="2.5" opacity="0.4"/>
              </g>
              <ellipse cx="170" cy="278" rx="88" ry="14" fill="#000000" opacity="0.15"/>
              <rect x="90" y="170" width="160" height="105" rx="6" fill="#1A2E1A"/>
              <rect x="90" y="170" width="160" height="105" rx="6" fill="none" stroke="#0F1D0F" stroke-width="1"/>
              <rect x="155" y="170" width="30" height="105" fill="#C9A84C"/>
              <rect x="90" y="205" width="160" height="24" fill="#C9A84C"/>
              <g transform="rotate(-14 170 172)">
                <rect x="82" y="140" width="176" height="34" rx="6" fill="url(#lidShine)"/>
                <rect x="82" y="140" width="176" height="34" rx="6" fill="none" stroke="#0F1D0F" stroke-width="1"/>
                <rect x="152" y="140" width="30" height="34" fill="#E8C96A"/>
              </g>
              <g transform="translate(170,142)">
                <path d="M0,0 C-18,-14 -32,-4 -20,6 C-10,13 -4,6 0,0 Z" fill="#E8C96A"/>
                <path d="M0,0 C18,-14 32,-4 20,6 C10,13 4,6 0,0 Z" fill="#E8C96A"/>
                <circle cx="0" cy="0" r="6" fill="#C9A84C"/>
              </g>
              <g transform="translate(212,95) rotate(18)">
                <path d="M0,14 C-10,4 -8,-10 6,-14 C14,-4 10,10 0,14 Z" fill="#8FAE6E"/>
                <line x1="0" y1="14" x2="4" y2="-10" stroke="#5C7A46" stroke-width="1"/>
              </g>
              <g fill="#E8C96A">
                <path d="M126 58 l3 9 l9 3 l-9 3 l-3 9 l-3-9 l-9-3 l9-3 Z"/>
                <path d="M240 118 l2.4 7 l7 2.4 l-7 2.4 l-2.4 7 l-2.4-7 l-7-2.4 l7-2.4 Z"/>
                <path d="M92 132 l2 6 l6 2 l-6 2 l-2 6 l-2-6 l-6-2 l6-2 Z" opacity="0.85"/>
              </g>
            </svg>

            <div class="prize-float p1">🍵</div>
            <div class="prize-float p2">🎁</div>
            <div class="prize-float p3">🌿</div>
            <div class="prize-float p4">🏆</div>
            <span class="sparkle s1">✦</span>
            <span class="sparkle s2">✦</span>
            <span class="sparkle s3">✦</span>
            <span class="sparkle s4">✦</span>
        </div>
    </div>

    <div class="hero-content">
        <div class="hero-eyebrow">Ouverture 10e boutique — Nice</div>

        <h1 class="hero-title">
            Le jeu-concours<br>
            <em>100% gagnant</em><br>
            Thé Tip Top
        </h1>

        <p class="hero-desc">
            Chaque achat supérieur à 49€ vous offre un code unique.
            Découvrez votre lot et vivez l'expérience Thé Tip Top —
            un rituel sensoriel autour de thés bio et handmade d'exception.
        </p>

        <div class="hero-btns">
            <?php if (!$is_logged || ($user && $user['role'] === 'client')): ?>
                <a href="/pages/participation.php" class="btn-primary-ttt">Je participe</a>
            <?php endif; ?>
            <?php if (!$is_logged): ?>
                <a href="/pages/inscription.php" class="btn-ghost-ttt">S'inscrire gratuitement</a>
            <?php elseif ($user && $user['role'] === 'admin'): ?>
                <a href="/pages/admin.php" class="btn-ghost-ttt">Administration</a>
            <?php elseif ($user && $user['role'] === 'employe'): ?>
                <a href="/pages/employe.php" class="btn-ghost-ttt">Espace boutique</a>
            <?php else: ?>
                <a href="/pages/mon-compte.php" class="btn-ghost-ttt">Mon compte</a>
            <?php endif; ?>
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
        <span class="bandeau-item">☕ Rituel sensoriel <span class="bandeau-sep">·</span></span>
        <span class="bandeau-item">🏙️ 10e boutique à Nice <span class="bandeau-sep">·</span></span>
        <span class="bandeau-item">🏆 500 000 codes gagnants <span class="bandeau-sep">·</span></span>
        <span class="bandeau-item">🌿 100% Bio <span class="bandeau-sep">·</span></span>
        <span class="bandeau-item">✋ Handmade <span class="bandeau-sep">·</span></span>
        <span class="bandeau-item">♻️ Éco-responsable <span class="bandeau-sep">·</span></span>
        <span class="bandeau-item">☕ Rituel sensoriel <span class="bandeau-sep">·</span></span>
        <span class="bandeau-item">🏙️ 10e boutique à Nice <span class="bandeau-sep">·</span></span>
        <span class="bandeau-item">🏆 500 000 codes gagnants <span class="bandeau-sep">·</span></span>
    </div>
</div>

<!-- ===================== NOTRE HISTOIRE ===================== -->
<section class="ttt-histoire reveal">
    <div class="histoire-visuel">
        <img
            src="https://images.pexels.com/photos/230477/pexels-photo-230477.jpeg?w=800&h=600&fit=crop"
            alt="Préparation artisanale du thé"
            loading="lazy"
        >
        <div class="histoire-visuel-overlay">
            <div class="histoire-badge-mini">
                <div class="badge-titre">Bio & Handmade</div>
                <div class="badge-sub">Depuis toujours</div>
            </div>
        </div>
    </div>
    <div class="histoire-texte">
        <div class="section-eyebrow">Notre histoire</div>
        <h2 class="section-title">
            L'art du thé,<br>façonné à <em>Nice</em>
        </h2>
        <p class="section-body">
            Depuis toujours, Thé Tip Top sélectionne des thés de très grande qualité :
            mélanges signatures, thés détox, thés blancs, thés aux légumes et infusions.
            Chaque gamme est certifiée bio et préparée à la main par nos artisans, dans
            le respect d'un savoir-faire exigeant.
        </p>
        <p class="section-body" style="margin-top: -0.8rem;">
            À l'occasion de l'ouverture de notre 10e boutique à Nice, nous transformons
            chaque achat en un rituel sensoriel : la vapeur qui s'élève, l'arôme des
            feuilles, le geste artisanal derrière chaque coffret.
        </p>
        <div class="badges-row">
            <span class="badge-pill">🌿 Bio certifié</span>
            <span class="badge-pill">✋ Handmade</span>
            <span class="badge-pill">♻️ RSE</span>
            <span class="badge-pill">🏙️ Nice</span>
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

    <?php if (!$is_logged || ($user && $user['role'] === 'client')): ?>
        <a href="/pages/participation.php" class="btn-primary-ttt">Je participe</a>
    <?php endif; ?>
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
            <p class="lot-desc">Une boîte de 100g d'un thé détox ou d'infusion bio.</p>
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
        <div class="lot-card grand-prix">
            <div>
                <div class="grand-prix-badge">✦ Grand Prix</div>
                <div class="lot-pct">1 an</div>
            </div>
            <div>
                <div class="lot-name">Un an de thé offert</div>
                <p class="lot-desc">
                    Valeur 360€ — Tirage au sort parmi tous les participants.
                    Le lot ultime pour les vrais amateurs de thé bio handmade.
                </p>
            </div>
        </div>
    </div>

    <?php if (!$is_logged || ($user && $user['role'] === 'client')): ?>
        <div style="text-align:center; margin-top:2.5rem;">
            <a href="/pages/participation.php" class="btn-primary-ttt">Participer maintenant</a>
        </div>
    <?php endif; ?>
</section>

<!-- ===================== 10e BOUTIQUE NICE ===================== -->
<section class="ttt-boutique reveal">
    <div class="boutique-texte">
        <div class="section-eyebrow">Notre 10e boutique</div>
        <h2 class="section-title" style="color: var(--creme);">
            Le rituel du thé bio<br>à <em style="color: var(--or-clair);">Nice</em>
        </h2>
        <ul class="boutique-list">
            <li>Mélanges signatures exclusifs</li>
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
            src="https://images.pexels.com/photos/1417945/pexels-photo-1417945.jpeg?w=800&h=600&fit=crop"
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
        <?php if (!$is_logged || ($user && $user['role'] === 'client')): ?>
            <a href="/pages/participation.php" class="btn-primary-ttt">Je participe</a>
        <?php endif; ?>
        <?php if (!$is_logged): ?>
            <a href="/pages/inscription.php" class="btn-ghost-ttt">Créer un compte</a>
        <?php elseif ($user && $user['role'] === 'admin'): ?>
            <a href="/pages/admin.php" class="btn-ghost-ttt">Administration</a>
        <?php elseif ($user && $user['role'] === 'employe'): ?>
            <a href="/pages/employe.php" class="btn-ghost-ttt">Espace boutique</a>
        <?php else: ?>
            <a href="/pages/mon-compte.php" class="btn-ghost-ttt">Mon compte</a>
        <?php endif; ?>
    </div>
</section>

<!-- ===================== SUIVEZ-NOUS ===================== -->
<section style="background: var(--blanc-casse); padding: 3rem 1.5rem; text-align: center;">
    <p style="font-size: 0.78rem; letter-spacing: 0.1em; text-transform: uppercase; color: #6a7f6a; margin-bottom: 1rem;">Suivez-nous</p>
    <div style="display: flex; gap: 16px; justify-content: center;">
        <a href="https://www.instagram.com/thetiptopoffi/" target="_blank" rel="noopener" aria-label="Instagram"
           style="display: flex; align-items: center; justify-content: center; width: 46px; height: 46px; border-radius: 50%; border: 1px solid rgba(45,74,45,0.2); transition: all 0.2s;"
           onmouseover="this.style.borderColor='var(--or)'; this.style.background='rgba(201,168,76,0.08)'"
           onmouseout="this.style.borderColor='rgba(45,74,45,0.2)'; this.style.background='transparent'">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect x="2" y="2" width="20" height="20" rx="5" stroke="#1A2E1A" stroke-width="1.8"/>
                <circle cx="12" cy="12" r="4.2" stroke="#1A2E1A" stroke-width="1.8"/>
                <circle cx="17.3" cy="6.7" r="1.1" fill="#1A2E1A"/>
            </svg>
        </a>
        <a href="https://www.facebook.com/share/1DPCFmBXrv/?mibextid=wwXIfr" target="_blank" rel="noopener" aria-label="Facebook"
           style="display: flex; align-items: center; justify-content: center; width: 46px; height: 46px; border-radius: 50%; border: 1px solid rgba(45,74,45,0.2); transition: all 0.2s;"
           onmouseover="this.style.borderColor='var(--or)'; this.style.background='rgba(201,168,76,0.08)'"
           onmouseout="this.style.borderColor='rgba(45,74,45,0.2)'; this.style.background='transparent'">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M15 8.5H17V5.5H15C13.067 5.5 11.5 7.067 11.5 9V11H9.5V14H11.5V19.5H14.5V14H16.5L17 11H14.5V9C14.5 8.724 14.724 8.5 15 8.5Z" fill="#1A2E1A"/>
            </svg>
        </a>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>