<?php
$page_title = 'Thé Tip Top — Jeu-Concours 100% Gagnant';
require_once __DIR__ . '/includes/header.php';
?>

<!-- HERO -->
<section class="hero">
    <div class="container">
        <div class="badge-dore">🎉 Ouverture de notre 10e boutique à Nice</div>
        <h1>Le Jeu-Concours<br><span style="color:#74C69D;">100% Gagnant</span></h1>
        <p class="lead mt-3 mb-4" style="color:rgba(255,255,255,0.85);">
            Chaque achat supérieur à 49€ vous donne un code gagnant.<br>
            Découvrez votre lot et réclamez-le en boutique ou en ligne.
        </p>
        <a href="/pages/participation.php" class="btn btn-tiptop btn-lg me-3">
            <i class="bi bi-gift me-2"></i>Entrer mon code
        </a>
        <?php if (!$user): ?>
            <a href="/pages/inscription.php" class="btn btn-outline-light btn-lg">
                S'inscrire gratuitement
            </a>
        <?php endif; ?>
    </div>
</section>

<!-- COMMENT PARTICIPER -->
<section class="py-5" style="background-color:var(--creme, #F5F0E8);">
    <div class="container">
        <h2 class="text-center fw-bold mb-5" style="color:#1B4332;">Comment participer ?</h2>
        <div class="row g-4 justify-content-center">
            <?php
            $etapes = [
                ['num'=>'1','icon'=>'bi-bag-check','titre'=>'Achetez','desc'=>'Effectuez un achat de plus de 49€ dans notre boutique de Nice.'],
                ['num'=>'2','icon'=>'bi-upc-scan','titre'=>'Récupérez votre code','desc'=>'Trouvez votre code unique à 10 caractères sur votre ticket de caisse.'],
                ['num'=>'3','icon'=>'bi-keyboard','titre'=>'Entrez votre code','desc'=>'Connectez-vous et entrez votre code sur notre site pour découvrir votre gain.'],
                ['num'=>'4','icon'=>'bi-trophy','titre'=>'Réclamez votre lot','desc'=>'Réclamez votre gain en magasin ou directement en ligne sous 30 jours.'],
            ];
            foreach($etapes as $e): ?>
            <div class="col-md-3 col-sm-6 text-center">
                <div class="etape-num"><?= $e['num'] ?></div>
                <i class="bi <?= $e['icon'] ?> fs-2 mb-2" style="color:#2D6A4F;"></i>
                <h5 class="fw-bold"><?= $e['titre'] ?></h5>
                <p class="text-muted small"><?= $e['desc'] ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- LES LOTS -->
<section class="py-5 bg-white">
    <div class="container">
        <h2 class="text-center fw-bold mb-2" style="color:#1B4332;">Les lots à gagner</h2>
        <p class="text-center text-muted mb-5">500 000 codes — 100% gagnants</p>
        <div class="row g-4">
            <?php
            $lots = [
                ['pct'=>'60%','icon'=>'bi-cup-hot','titre'=>'Infuseur à thé','desc'=>'Un infuseur premium pour préparer votre thé à la perfection.','color'=>'#1B4332'],
                ['pct'=>'20%','icon'=>'bi-stars','titre'=>'Thé détox 100g','desc'=>'Une boîte de 100g d\'un thé détox ou d\'infusion bio.','color'=>'#2D6A4F'],
                ['pct'=>'10%','icon'=>'bi-award','titre'=>'Thé signature 100g','desc'=>'Une boîte de 100g d\'un thé signature handmade.','color'=>'#B5860D'],
                ['pct'=>'6%','icon'=>'bi-gift','titre'=>'Coffret 39€','desc'=>'Un coffret découverte d\'une valeur de 39€.','color'=>'#E76F51'],
                ['pct'=>'4%','icon'=>'bi-gift-fill','titre'=>'Coffret 69€','desc'=>'Un coffret découverte premium d\'une valeur de 69€.','color'=>'#E63946'],
            ];
            foreach($lots as $lot): ?>
            <div class="col-md-4 col-sm-6">
                <div class="lot-card">
                    <div class="lot-pct" style="background:<?= $lot['color'] ?>;"><?= $lot['pct'] ?></div>
                    <i class="bi <?= $lot['icon'] ?> lot-icon"></i>
                    <h5 class="fw-bold"><?= $lot['titre'] ?></h5>
                    <p class="text-muted small"><?= $lot['desc'] ?></p>
                </div>
            </div>
            <?php endforeach; ?>
            <div class="col-md-4 col-sm-6">
                <div class="lot-card" style="border-color:#B5860D; background: linear-gradient(135deg,#1B4332,#2D6A4F); color:white;">
                    <div class="lot-pct" style="background:#B5860D;">Grand Prix</div>
                    <i class="bi bi-trophy-fill lot-icon" style="color:#B5860D;"></i>
                    <h5 class="fw-bold text-white">1 an de thé</h5>
                    <p class="small" style="color:rgba(255,255,255,0.8);">Valeur 360€. Tirage au sort parmi tous les participants à l'issue du jeu.</p>
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

<!-- SECTION THÉ TIP TOP -->
<section class="py-5" style="background-color:#F5F0E8;">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-md-6">
                <h2 class="fw-bold" style="color:#1B4332;">Des thés bio et handmade d'exception</h2>
                <p class="text-muted">Thé Tip Top propose une gamme de thés de très grande qualité — mélanges signatures, thés détox, thés blancs, infusions — tous bios et faits à la main.</p>
                <ul class="list-unstyled">
                    <li class="mb-2"><i class="bi bi-check-circle-fill me-2" style="color:#2D6A4F;"></i>100% Bio certifié</li>
                    <li class="mb-2"><i class="bi bi-check-circle-fill me-2" style="color:#2D6A4F;"></i>Handmade — fait à la main</li>
                    <li class="mb-2"><i class="bi bi-check-circle-fill me-2" style="color:#2D6A4F;"></i>Mélanges signatures exclusifs</li>
                    <li class="mb-2"><i class="bi bi-check-circle-fill me-2" style="color:#2D6A4F;"></i>Démarche éco-responsable RSE</li>
                </ul>
            </div>
            <div class="col-md-6 text-center">
                <div style="background:#1B4332; border-radius:16px; padding:40px; color:white;">
                    <i class="bi bi-geo-alt-fill fs-1" style="color:#B5860D;"></i>
                    <h4 class="mt-3">10e Boutique — Nice</h4>
                    <p class="text-white-50">Venez nous rendre visite et repartez avec votre code gagnant !</p>
                    <div class="mt-3">
                        <span class="badge bg-success me-2">Jeu du 30 jours</span>
                        <span class="badge" style="background:#B5860D;">100% gagnant</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
