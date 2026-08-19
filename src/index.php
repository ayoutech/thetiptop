<?php
$page_title = 'Thé Tip Top — Jeu-Concours 100% Gagnant | Thés du Sahara Marocain';
require_once __DIR__ . '/includes/header.php';
?>

<!-- HERO -->
<section class="hero">
    <div class="container position-relative">
        <div class="hero-badge">🌙 Ouverture de notre 10e boutique à Nice</div>
        <h1>Le Jeu-Concours<br><span>100% Gagnant</span></h1>
        <p>Chaque achat supérieur à 49€ vous offre un code unique.<br>
        Découvrez votre lot et vivez l'expérience Thé Tip Top.</p>
        <div class="d-flex gap-3 justify-content-center flex-wrap">
            <a href="/pages/participation.php" class="btn btn-tiptop btn-lg">
                <i class="bi bi-gift me-2"></i>Entrer mon code
            </a>
            <?php if (!$user): ?>
            <a href="/pages/inscription.php" class="btn btn-outline-light btn-lg" style="border-radius:50px;">
                S'inscrire gratuitement
            </a>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- HISTOIRE DE LA MARQUE -->
<section class="section-histoire">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <div class="histoire-card">
                    <h2>Né au cœur du Sahara Marocain</h2>
                    <div class="motif-marocain"></div>
                    <p>
                        L'histoire de Thé Tip Top commence dans les médinas dorées de Marrakech et de Fès,
                        où les souffles chauds du désert portent le parfum des feuilles de thé séchées au soleil.
                        Inspirés par les traditions ancestrales des Touaregs et des Berbères, nos artisans
                        préparent chaque mélange à la main, selon des recettes transmises depuis des siècles.
                    </p>
                    <p>
                        Des oasis verdoyantes de l'Atlas aux jardins secrets de l'Anti-Atlas, nous sélectionnons
                        les feuilles les plus nobles, récoltées à l'aube pour préserver tous leurs arômes.
                        Chaque tasse de Thé Tip Top est un voyage sensoriel entre tradition et modernité.
                    </p>
                    <div class="d-flex gap-2 flex-wrap mt-3">
                        <span class="origine-badge">🌍 Origine Maroc</span>
                        <span class="origine-badge">🌿 100% Bio</span>
                        <span class="origine-badge">✋ Handmade</span>
                        <span class="origine-badge">♻️ Éco-responsable</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="row g-3">
                    <?php
                    $valeurs = [
                        ['icon'=>'🌙', 'titre'=>'Tradition Berbère', 'desc'=>'Recettes ancestrales transmises de génération en génération dans les médinas du Maroc.'],
                        ['icon'=>'☀️', 'titre'=>'Séché au Soleil', 'desc'=>'Feuilles récoltées à l\'aube et séchées naturellement sous le soleil du Sahara.'],
                        ['icon'=>'🏺', 'titre'=>'Préparation Artisanale', 'desc'=>'Chaque mélange est préparé à la main par nos artisans dans le respect des traditions.'],
                        ['icon'=>'🌿', 'titre'=>'Bio Certifié', 'desc'=>'Aucun pesticide, aucun engrais chimique. La nature dans toute sa pureté.'],
                    ];
                    foreach($valeurs as $v): ?>
                    <div class="col-6">
                        <div class="valeur-item">
                            <div class="valeur-icon"><?= $v['icon'] ?></div>
                            <div>
                                <h6 class="fw-bold mb-1" style="color:var(--vert-sahara);"><?= $v['titre'] ?></h6>
                                <p class="small text-muted mb-0"><?= $v['desc'] ?></p>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- COMMENT PARTICIPER -->
<section class="section-etapes">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold" style="font-family:'Playfair Display',serif; color:var(--vert-sahara);">Comment participer ?</h2>
            <div class="motif-marocain"></div>
        </div>
        <div class="row g-4 justify-content-center">
            <?php
            $etapes = [
                ['num'=>'1','icon'=>'bi-bag-check','titre'=>'Achetez','desc'=>'Effectuez un achat de plus de 49€ dans notre boutique de Nice.'],
                ['num'=>'2','icon'=>'bi-upc-scan','titre'=>'Récupérez votre code','desc'=>'Trouvez votre code unique à 10 caractères sur votre ticket de caisse.'],
                ['num'=>'3','icon'=>'bi-keyboard','titre'=>'Entrez votre code','desc'=>'Connectez-vous et entrez votre code pour découvrir votre gain.'],
                ['num'=>'4','icon'=>'bi-trophy','titre'=>'Réclamez votre lot','desc'=>'Réclamez votre gain en magasin ou en ligne sous 30 jours.'],
            ];
            foreach($etapes as $e): ?>
            <div class="col-md-3 col-sm-6 text-center">
                <div class="etape-num"><?= $e['num'] ?></div>
                <i class="bi <?= $e['icon'] ?> fs-2 mb-2" style="color:var(--vert-sahara);"></i>
                <h5 class="fw-bold" style="font-family:'Playfair Display',serif;"><?= $e['titre'] ?></h5>
                <p class="text-muted small"><?= $e['desc'] ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- LES LOTS -->
<section class="section-lots">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold" style="font-family:'Playfair Display',serif; color:var(--vert-sahara);">Les lots à gagner</h2>
            <div class="motif-marocain"></div>
            <p class="text-muted">500 000 codes — 100% gagnants</p>
        </div>
        <div class="row g-4">
            <?php
            $lots = [
                ['pct'=>'60%','icon'=>'bi-cup-hot','titre'=>'Infuseur à thé','desc'=>'Un infuseur premium pour préparer votre thé à la perfection.'],
                ['pct'=>'20%','icon'=>'bi-stars','titre'=>'Thé détox 100g','desc'=>'Une boîte de 100g d\'un thé détox ou d\'infusion bio du Maroc.'],
                ['pct'=>'10%','icon'=>'bi-award','titre'=>'Thé signature 100g','desc'=>'Un de nos mélanges signatures exclusifs, handmade.'],
                ['pct'=>'6%','icon'=>'bi-gift','titre'=>'Coffret 39€','desc'=>'Un coffret découverte de nos thés d\'exception.'],
                ['pct'=>'4%','icon'=>'bi-gift-fill','titre'=>'Coffret 69€','desc'=>'Notre coffret premium — une expérience sensorielle complète.'],
            ];
            foreach($lots as $lot): ?>
            <div class="col-md-4 col-sm-6">
                <div class="lot-card">
                    <span class="lot-pct"><?= $lot['pct'] ?></span>
                    <i class="bi <?= $lot['icon'] ?> lot-icon"></i>
                    <h5 class="fw-bold" style="font-family:'Playfair Display',serif;"><?= $lot['titre'] ?></h5>
                    <p class="text-muted small"><?= $lot['desc'] ?></p>
                </div>
            </div>
            <?php endforeach; ?>
            <!-- Grand prix -->
            <div class="col-md-4 col-sm-6">
                <div class="lot-card" style="background:linear-gradient(135deg,#1a3a1a,var(--vert-sahara));color:white;border:2px solid var(--or);">
                    <span class="lot-pct" style="background:linear-gradient(135deg,var(--or),var(--or-clair));color:#1a3a1a;">Grand Prix 🌙</span>
                    <i class="bi bi-trophy-fill lot-icon" style="color:var(--or-clair);"></i>
                    <h5 class="fw-bold text-white" style="font-family:'Playfair Display',serif;">1 an de thé</h5>
                    <p class="small" style="color:rgba(255,255,255,0.8);">Valeur 360€ — Tirage au sort parmi tous les participants.</p>
                </div>
            </div>
        </div>
        <div class="text-center mt-5">
            <a href="/pages/participation.php" class="btn btn-tiptop btn-lg">
                <i class="bi bi-gift me-2"></i>Entrer mon code maintenant
            </a>
        </div>
    </div>
</section>

<!-- SECTION BOUTIQUE NICE -->
<section class="section-maroc">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <h2 class="fw-bold mb-3" style="font-family:'Playfair Display',serif; color:var(--vert-sahara);">
                    La magie du Maroc<br>à Nice
                </h2>
                <div class="motif-marocain" style="margin:0 0 20px;"></div>
                <p class="text-muted mb-4">
                    Notre 10e boutique ouvre ses portes à Nice, apportant avec elle les saveurs 
                    authentiques du Sahara marocain. Un espace chaleureux où chaque tasse raconte 
                    une histoire millénaire.
                </p>
                <div class="d-flex flex-column gap-3">
                    <?php
                    $features = [
                        ['icon'=>'bi-check-circle-fill', 'text'=>'Mélanges signatures exclusifs du Maroc'],
                        ['icon'=>'bi-check-circle-fill', 'text'=>'100% Bio — certifié par des organismes indépendants'],
                        ['icon'=>'bi-check-circle-fill', 'text'=>'Handmade — préparé à la main par nos artisans'],
                        ['icon'=>'bi-check-circle-fill', 'text'=>'Démarche RSE et éco-responsable'],
                    ];
                    foreach($features as $f): ?>
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi <?= $f['icon'] ?>" style="color:var(--or); font-size:1.2rem;"></i>
                        <span><?= $f['text'] ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="boutique-card">
                    <div style="font-size:3rem;margin-bottom:15px;">🌙</div>
                    <h4 class="fw-bold mb-2" style="font-family:'Playfair Display',serif;">10e Boutique — Nice</h4>
                    <p style="color:rgba(255,255,255,0.8);" class="mb-4">
                        Venez découvrir notre univers et repartez avec votre code gagnant !
                    </p>
                    <div class="d-flex justify-content-center gap-3 flex-wrap">
                        <span class="badge" style="background:rgba(201,148,42,0.2);color:var(--or-clair);border:1px solid var(--or);padding:8px 16px;border-radius:50px;">30 jours de jeu</span>
                        <span class="badge" style="background:rgba(201,148,42,0.2);color:var(--or-clair);border:1px solid var(--or);padding:8px 16px;border-radius:50px;">100% gagnant</span>
                        <span class="badge" style="background:rgba(201,148,42,0.2);color:var(--or-clair);border:1px solid var(--or);padding:8px 16px;border-radius:50px;">500 000 codes</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
