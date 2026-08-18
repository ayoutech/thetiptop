<?php
$page_title = 'Participer — Thé Tip Top';
require_once __DIR__ . '/../includes/header.php';

if (!$user) {
    header('Location: /pages/connexion.php?redirect=/pages/participation.php');
    exit;
}

$error = '';
$gain = null;
$gains_labels = [
    'infuseur'       => ['label' => 'Infuseur à thé', 'icon' => 'bi-cup-hot', 'desc' => 'Un infuseur premium pour préparer votre thé.'],
    'the_detox'      => ['label' => 'Thé détox 100g', 'icon' => 'bi-stars', 'desc' => 'Une boîte de 100g de thé détox ou d\'infusion bio.'],
    'the_signature'  => ['label' => 'Thé signature 100g', 'icon' => 'bi-award', 'desc' => 'Une boîte de 100g de thé signature handmade.'],
    'coffret_39'     => ['label' => 'Coffret découverte 39€', 'icon' => 'bi-gift', 'desc' => 'Un coffret découverte d\'une valeur de 39€.'],
    'coffret_69'     => ['label' => 'Coffret découverte 69€', 'icon' => 'bi-gift-fill', 'desc' => 'Un coffret découverte premium d\'une valeur de 69€.'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = strtoupper(trim($_POST['code'] ?? ''));

    if (strlen($code) !== 10) {
        $error = 'Le code doit contenir exactement 10 caractères.';
    } elseif (!preg_match('/^[A-Z0-9]{10}$/', $code)) {
        $error = 'Le code ne doit contenir que des lettres et des chiffres.';
    } else {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM tickets WHERE code = ?");
        $stmt->execute([$code]);
        $ticket = $stmt->fetch();

        if (!$ticket) {
            $error = 'Ce code est invalide. Vérifiez votre ticket de caisse.';
        } elseif ($ticket['utilise']) {
            $error = 'Ce code a déjà été utilisé.';
        } else {
            $stmt = $db->prepare("UPDATE tickets SET utilise = 1, user_id = ?, date_utilisation = NOW() WHERE code = ?");
            $stmt->execute([$user['id'], $code]);

            // Ajout au tirage final si pas déjà dedans
            $stmt = $db->prepare("SELECT id FROM tirage_final WHERE user_id = ?");
            $stmt->execute([$user['id']]);
            if (!$stmt->fetch()) {
                $stmt = $db->prepare("INSERT INTO tirage_final (user_id) VALUES (?)");
                $stmt->execute([$user['id']]);
            }

            $gain = $gains_labels[$ticket['gain']];
            $gain['code'] = $code;
        }
    }
}
?>

<main class="py-5">
    <div class="container">

        <?php if (isset($_GET['welcome'])): ?>
            <div class="alert alert-tiptop text-center mb-4">
                <i class="bi bi-hand-thumbs-up me-2"></i>
                Bienvenue <?= htmlspecialchars($user['prenom']) ?> ! Votre compte est créé. Entrez votre code pour découvrir votre gain !
            </div>
        <?php endif; ?>

        <?php if ($gain): ?>
            <!-- AFFICHAGE DU GAIN -->
            <div class="text-center mb-4">
                <h2 class="fw-bold" style="color:#1B4332;">Félicitations <?= htmlspecialchars($user['prenom']) ?> !</h2>
                <p class="text-muted">Vous avez gagné :</p>
            </div>
            <div class="gain-display">
                <div class="gain-icon"><i class="bi <?= $gain['icon'] ?>"></i></div>
                <h3><?= htmlspecialchars($gain['label']) ?></h3>
                <p><?= htmlspecialchars($gain['desc']) ?></p>
                <div class="mt-3">
                    <span class="badge bg-light text-dark fs-6">Code : <?= htmlspecialchars($gain['code']) ?></span>
                </div>
            </div>
            <div class="text-center mt-4">
                <p class="text-muted">Vous pouvez réclamer votre lot en boutique ou en ligne sous 30 jours.</p>
                <a href="/pages/mon-compte.php" class="btn btn-tiptop me-3">
                    <i class="bi bi-person me-2"></i>Voir mon compte
                </a>
                <a href="/pages/participation.php" class="btn btn-outline-secondary">
                    Entrer un autre code
                </a>
            </div>

        <?php else: ?>
            <!-- FORMULAIRE DE PARTICIPATION -->
            <div class="form-card" style="max-width:520px;">
                <h2 class="text-center"><i class="bi bi-upc-scan me-2"></i>Entrer mon code</h2>
                <p class="text-center text-muted small mb-4">
                    Trouvez votre code à 10 caractères sur votre ticket de caisse (achat > 49€)
                </p>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-4">
                        <label class="form-label fw-semibold text-center d-block">Votre code</label>
                        <input type="text" id="code-input" name="code" class="form-control code-input"
                               maxlength="10" placeholder="EX: AB12CD34EF"
                               value="<?= htmlspecialchars($_POST['code'] ?? '') ?>" required>
                        <p class="text-muted small text-center mt-2">10 caractères — lettres et chiffres uniquement</p>
                    </div>
                    <button type="submit" class="btn btn-tiptop w-100 btn-lg">
                        <i class="bi bi-search me-2"></i>Vérifier mon code
                    </button>
                </form>

                <hr>
                <div class="text-center">
                    <a href="/pages/mon-compte.php" class="text-muted small">
                        <i class="bi bi-clock-history me-1"></i>Voir l'historique de mes codes
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
