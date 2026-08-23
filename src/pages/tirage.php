<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$page_title = 'Tirage au sort final — Thé Tip Top';
require_once __DIR__ . '/../includes/header.php';

if (!$user || $user['role'] !== 'admin') {
    header('Location: /'); exit;
}

$db = getDB();
$error = '';
$success = '';
$gagnant = null;

// Vérifie si un gagnant a déjà été tiré (colonne gagnant sur tirage_final)
$stmt = $db->query("SHOW COLUMNS FROM tirage_final LIKE 'gagnant'");
if ($stmt->rowCount() === 0) {
    $db->exec("ALTER TABLE tirage_final ADD COLUMN gagnant TINYINT(1) DEFAULT 0");
    $db->exec("ALTER TABLE tirage_final ADD COLUMN date_tirage DATETIME NULL");
}

// Lancer le tirage
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['lancer_tirage'])) {
    // Vérifie qu'aucun tirage n'a déjà été fait
    $stmt = $db->query("SELECT COUNT(*) FROM tirage_final WHERE gagnant = 1");
    if ($stmt->fetchColumn() > 0) {
        $error = 'Un tirage au sort a déjà été effectué. Consultez le gagnant ci-dessous.';
    } else {
        $stmt = $db->query("SELECT id, user_id FROM tirage_final ORDER BY id ASC");
        $participants = $stmt->fetchAll();

        if (empty($participants)) {
            $error = 'Aucun participant inscrit au tirage final pour le moment.';
        } else {
            // Tirage aléatoire cryptographiquement sûr côté serveur
            $index = random_int(0, count($participants) - 1);
            $gagnant_id = $participants[$index]['id'];

            $stmt = $db->prepare("UPDATE tirage_final SET gagnant = 1, date_tirage = NOW() WHERE id = ?");
            $stmt->execute([$gagnant_id]);

            $success = 'Tirage effectué avec succès parmi ' . count($participants) . ' participant(s).';
        }
    }
}

// Récupérer le gagnant s'il existe
$stmt = $db->query("
    SELECT u.prenom, u.nom, u.email, tf.date_tirage
    FROM tirage_final tf
    JOIN users u ON tf.user_id = u.id
    WHERE tf.gagnant = 1
    LIMIT 1
");
$gagnant = $stmt->fetch();

$total_participants = $db->query("SELECT COUNT(*) FROM tirage_final")->fetchColumn();
?>

<style>
.tirage-page {
    background: var(--blanc-casse);
    min-height: calc(100vh - 68px);
    padding: 3rem 2rem;
    display: flex;
    justify-content: center;
}
.tirage-container { max-width: 640px; width: 100%; }
.tirage-title {
    font-family: 'Playfair Display', serif;
    font-size: 1.8rem;
    font-weight: 700;
    color: var(--vert-nuit);
    margin-bottom: 0.3rem;
    text-align: center;
}
.tirage-sub {
    font-size: 0.85rem;
    color: #6a7f6a;
    text-align: center;
    margin-bottom: 2rem;
}
.tirage-stat-box {
    background: #fff;
    border: 1px solid rgba(45,74,45,0.1);
    border-radius: 4px;
    padding: 1.5rem;
    text-align: center;
    margin-bottom: 1.5rem;
}
.tirage-stat-num {
    font-family: 'Playfair Display', serif;
    font-size: 2.2rem;
    font-weight: 700;
    color: var(--or);
}
.tirage-stat-label {
    font-size: 0.75rem;
    color: #6a7f6a;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    margin-top: 4px;
}
.btn-tirage {
    display: block;
    width: 100%;
    background: var(--vert-nuit);
    color: var(--creme);
    padding: 16px;
    border: none;
    border-radius: 2px;
    font-size: 0.9rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    cursor: pointer;
    transition: background 0.2s;
    text-align: center;
}
.btn-tirage:hover { background: var(--or); color: var(--vert-nuit); }
.btn-tirage:disabled {
    background: #ccc;
    cursor: not-allowed;
}
.alert-ttt {
    padding: 12px 16px;
    border-radius: 2px;
    font-size: 0.85rem;
    margin-bottom: 1.5rem;
    text-align: center;
}
.alert-erreur { background: #fdf0f0; border: 1px solid #f0c0c0; color: #8b2020; }
.alert-ok { background: #f0fdf4; border: 1px solid #c0e0c8; color: #1a4a2a; }

.gagnant-card {
    background: var(--vert-nuit);
    border-radius: 4px;
    padding: 2.5rem 2rem;
    text-align: center;
    box-shadow: 0 8px 40px rgba(26,46,26,0.2);
}
.gagnant-eyebrow {
    font-size: 0.68rem;
    font-weight: 600;
    letter-spacing: 0.16em;
    text-transform: uppercase;
    color: var(--or);
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}
.gagnant-eyebrow::before, .gagnant-eyebrow::after {
    content: '';
    display: inline-block;
    width: 20px; height: 1px;
    background: var(--or);
}
.gagnant-icon { font-size: 3rem; margin-bottom: 1rem; }
.gagnant-nom {
    font-family: 'Playfair Display', serif;
    font-size: 1.6rem;
    font-weight: 700;
    color: var(--creme);
    margin-bottom: 0.4rem;
}
.gagnant-email {
    font-size: 0.85rem;
    color: rgba(245,237,214,0.6);
    margin-bottom: 1rem;
}
.gagnant-date {
    font-size: 0.72rem;
    color: rgba(245,237,214,0.4);
}
</style>

<div class="tirage-page">
    <div class="tirage-container">
        <div class="tirage-title">🏆 Tirage au sort — Grand Prix</div>
        <div class="tirage-sub">Un an de thé offert — Valeur 360€</div>

        <?php if ($error): ?>
            <div class="alert-ttt alert-erreur"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert-ttt alert-ok">✅ <?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <?php if ($gagnant): ?>
            <div class="gagnant-card">
                <div class="gagnant-eyebrow">Gagnant du Grand Prix</div>
                <div class="gagnant-icon">🏆</div>
                <div class="gagnant-nom"><?= htmlspecialchars($gagnant['prenom'] . ' ' . $gagnant['nom']) ?></div>
                <div class="gagnant-email"><?= htmlspecialchars($gagnant['email']) ?></div>
                <div class="gagnant-date">
                    Tirage effectué le <?= date('d/m/Y à H:i', strtotime($gagnant['date_tirage'])) ?>
                    sous le contrôle de Maître Arnaud Rick, huissier de justice.
                </div>
            </div>
        <?php else: ?>
            <div class="tirage-stat-box">
                <div class="tirage-stat-num"><?= number_format($total_participants) ?></div>
                <div class="tirage-stat-label">Participants inscrits au tirage</div>
            </div>

            <form method="POST" onsubmit="return confirm('Confirmer le lancement du tirage au sort ? Cette action est définitive et ne pourra être refaite.');">
                <button type="submit" name="lancer_tirage" value="1" class="btn-tirage"
                        <?= $total_participants == 0 ? 'disabled' : '' ?>>
                    🎲 Lancer le tirage au sort
                </button>
            </form>
            <p style="text-align:center; font-size:0.72rem; color:#9aaa9a; margin-top:1rem;">
                Le tirage est aléatoire, définitif et ne peut être relancé une fois effectué.
            </p>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
