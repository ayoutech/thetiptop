<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header('Location: /pages/connexion.php?redirect=/pages/participation.php');
    exit;
}

$page_title = 'Participer — Thé Tip Top';
require_once __DIR__ . '/../includes/header.php';

$error = '';
$gain = null;
$gains_labels = [
    'infuseur'      => ['label' => 'Infuseur à thé',          'icon' => '🍵', 'desc' => 'Un infuseur premium pour préparer votre thé à la perfection.'],
    'the_detox'     => ['label' => 'Thé détox 100g',          'icon' => '🌿', 'desc' => 'Une boîte de 100g de thé détox ou d\'infusion bio du Maroc.'],
    'the_signature' => ['label' => 'Thé signature 100g',      'icon' => '⭐', 'desc' => 'Une boîte de 100g de thé signature handmade exclusif.'],
    'coffret_39'    => ['label' => 'Coffret découverte 39€',  'icon' => '🎁', 'desc' => 'Un coffret découverte d\'une valeur de 39€.'],
    'coffret_69'    => ['label' => 'Coffret découverte 69€',  'icon' => '✨', 'desc' => 'Un coffret découverte premium d\'une valeur de 69€.'],
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

<style>
.participation-section {
    min-height: calc(100vh - 68px);
    background: var(--blanc-casse);
    padding: 3rem 1.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
}
.participation-card {
    background: #fff;
    border: 1px solid rgba(45,74,45,0.1);
    border-radius: 4px;
    padding: 3rem 2.5rem;
    width: 100%;
    max-width: 520px;
    box-shadow: 0 4px 32px rgba(26,46,26,0.06);
}
.part-title {
    font-family: 'Playfair Display', serif;
    font-size: 1.8rem;
    font-weight: 700;
    color: var(--vert-nuit);
    text-align: center;
    margin-bottom: 0.4rem;
}
.part-sub {
    font-size: 0.83rem;
    color: #6a7f6a;
    text-align: center;
    margin-bottom: 2rem;
}
.form-group { margin-bottom: 1.2rem; }
.form-label {
    display: block;
    font-size: 0.78rem;
    font-weight: 600;
    color: var(--vert-nuit);
    margin-bottom: 0.4rem;
    letter-spacing: 0.02em;
    text-align: center;
}
.code-input-ttt {
    width: 100%;
    padding: 16px;
    border: 2px solid rgba(45,74,45,0.2);
    border-radius: 2px;
    font-size: 1.4rem;
    font-weight: 700;
    letter-spacing: 0.2em;
    text-align: center;
    font-family: 'Inter', monospace;
    color: var(--vert-nuit);
    background: #fff;
    outline: none;
    text-transform: uppercase;
    transition: border-color 0.2s, box-shadow 0.2s;
}
.code-input-ttt:focus {
    border-color: var(--or);
    box-shadow: 0 0 0 4px rgba(201,168,76,0.12);
}
.code-input-ttt::placeholder {
    color: #c8d8c8;
    font-weight: 400;
    letter-spacing: 0.1em;
    font-size: 1rem;
}
.code-hint {
    font-size: 0.72rem;
    color: #9aaa9a;
    text-align: center;
    margin-top: 0.5rem;
}
.btn-submit-ttt {
    width: 100%;
    background: var(--vert-nuit);
    color: var(--creme);
    padding: 14px;
    border: none;
    border-radius: 2px;
    font-size: 0.82rem;
    font-weight: 700;
    letter-spacing: 0.09em;
    text-transform: uppercase;
    cursor: pointer;
    transition: background 0.2s;
    margin-top: 0.5rem;
}
.btn-submit-ttt:hover { background: var(--or); color: var(--vert-nuit); }
.alert-ttt {
    padding: 12px 16px;
    border-radius: 2px;
    font-size: 0.82rem;
    margin-bottom: 1.5rem;
    text-align: center;
}
.alert-erreur { background: #fdf0f0; border: 1px solid #f0c0c0; color: #8b2020; }
.part-footer {
    text-align: center;
    margin-top: 1.5rem;
    padding-top: 1.5rem;
    border-top: 1px solid rgba(45,74,45,0.08);
    font-size: 0.78rem;
    color: #9aaa9a;
}
.part-footer a { color: var(--or); text-decoration: none; }

/* GAIN */
.gain-section {
    min-height: calc(100vh - 68px);
    background: var(--blanc-casse);
    padding: 3rem 1.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
}
.gain-card {
    background: var(--vert-nuit);
    border-radius: 4px;
    padding: 3.5rem 2.5rem;
    width: 100%;
    max-width: 520px;
    text-align: center;
    box-shadow: 0 8px 48px rgba(26,46,26,0.2);
}
.gain-eyebrow {
    font-size: 0.68rem;
    font-weight: 600;
    letter-spacing: 0.16em;
    text-transform: uppercase;
    color: var(--or);
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}
.gain-eyebrow::before, .gain-eyebrow::after {
    content: '';
    display: inline-block;
    width: 20px; height: 1px;
    background: var(--or);
}
.gain-icon-big { font-size: 4rem; margin-bottom: 1rem; display: block; }
.gain-titre {
    font-family: 'Playfair Display', serif;
    font-size: 2rem;
    font-weight: 700;
    color: var(--creme);
    margin-bottom: 0.5rem;
}
.gain-desc {
    font-size: 0.88rem;
    color: rgba(245,237,214,0.6);
    margin-bottom: 1.5rem;
    line-height: 1.6;
}
.gain-code-badge {
    display: inline-block;
    background: rgba(201,168,76,0.15);
    border: 1px solid rgba(201,168,76,0.3);
    color: var(--or-clair);
    padding: 8px 20px;
    border-radius: 2px;
    font-size: 0.85rem;
    font-weight: 700;
    letter-spacing: 0.12em;
    margin-bottom: 2rem;
    font-family: monospace;
}
.gain-note {
    font-size: 0.75rem;
    color: rgba(245,237,214,0.35);
    margin-bottom: 2rem;
}
.gain-btns { display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap; }
.btn-or {
    background: var(--or);
    color: var(--vert-nuit);
    padding: 12px 28px;
    border: none;
    border-radius: 2px;
    font-size: 0.8rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    text-decoration: none;
    display: inline-block;
    transition: background 0.2s;
}
.btn-or:hover { background: var(--or-clair); color: var(--vert-nuit); }
.btn-outline-light-ttt {
    background: transparent;
    color: rgba(245,237,214,0.6);
    padding: 11px 24px;
    border: 1px solid rgba(245,237,214,0.2);
    border-radius: 2px;
    font-size: 0.8rem;
    font-weight: 500;
    text-decoration: none;
    display: inline-block;
    transition: all 0.2s;
}
.btn-outline-light-ttt:hover { border-color: var(--or); color: var(--or); }
</style>

<?php if ($gain): ?>
<section class="gain-section">
    <div class="gain-card">
        <div class="gain-eyebrow">Félicitations <?= htmlspecialchars($user['prenom']) ?></div>
        <span class="gain-icon-big"><?= $gain['icon'] ?></span>
        <h2 class="gain-titre">Vous avez gagné !</h2>
        <p class="gain-desc"><?= htmlspecialchars($gain['label']) ?><br><?= htmlspecialchars($gain['desc']) ?></p>
        <div class="gain-code-badge">Code : <?= htmlspecialchars($gain['code']) ?></div>
        <p class="gain-note">Réclamez votre lot en boutique ou en ligne sous 30 jours.</p>
        <div class="gain-btns">
            <a href="/pages/mon-compte.php" class="btn-or">Voir mon compte</a>
            <a href="/pages/participation.php" class="btn-outline-light-ttt">Entrer un autre code</a>
        </div>
    </div>
</section>

<?php else: ?>
<section class="participation-section">
    <div class="participation-card">
        <h1 class="part-title">Entrer mon code</h1>
        <p class="part-sub">Trouvez votre code à 10 caractères sur votre ticket de caisse (achat &gt; 49€)</p>

        <?php if ($error): ?>
            <div class="alert-ttt alert-erreur"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label class="form-label">Votre code unique</label>
                <input type="text" id="code-input" name="code"
                       class="code-input-ttt"
                       maxlength="10"
                       placeholder="AB12CD34EF"
                       value="<?= htmlspecialchars($_POST['code'] ?? '') ?>"
                       required autocomplete="off">
                <p class="code-hint">10 caractères — lettres et chiffres uniquement</p>
            </div>
            <button type="submit" class="btn-submit-ttt">Vérifier mon code</button>
        </form>

        <div class="part-footer">
            <a href="/pages/mon-compte.php">Voir l'historique de mes gains</a>
        </div>
    </div>
</section>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>