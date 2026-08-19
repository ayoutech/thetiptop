<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header('Location: /pages/connexion.php');
    exit;
}

$page_title = 'Mon Compte — Thé Tip Top';
require_once __DIR__ . '/../includes/header.php';

$db = getDB();
$stmt = $db->prepare("SELECT * FROM tickets WHERE user_id = ? ORDER BY date_utilisation DESC");
$stmt->execute([$user['id']]);
$tickets = $stmt->fetchAll();

$gains_labels = [
    'infuseur'      => ['label' => 'Infuseur à thé', 'icon' => '🍵', 'color' => '#2D4A2D'],
    'the_detox'     => ['label' => 'Thé détox 100g', 'icon' => '🌿', 'color' => '#3D6B3D'],
    'the_signature' => ['label' => 'Thé signature 100g', 'icon' => '⭐', 'color' => '#C9A84C'],
    'coffret_39'    => ['label' => 'Coffret 39€', 'icon' => '🎁', 'color' => '#8B6914'],
    'coffret_69'    => ['label' => 'Coffret 69€', 'icon' => '✨', 'color' => '#1A2E1A'],
];

$stmt2 = $db->prepare("SELECT * FROM tirage_final WHERE user_id = ?");
$stmt2->execute([$user['id']]);
$tirage = $stmt2->fetch();
?>

<style>
.compte-section {
    min-height: calc(100vh - 68px);
    background: var(--blanc-casse);
    padding: 3rem 1.5rem;
}
.compte-grid {
    max-width: 900px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: 280px 1fr;
    gap: 2rem;
}
.profil-card {
    background: #fff;
    border: 1px solid rgba(45,74,45,0.1);
    border-radius: 4px;
    padding: 2rem;
    text-align: center;
    box-shadow: 0 4px 24px rgba(26,46,26,0.06);
    height: fit-content;
}
.profil-avatar {
    width: 72px; height: 72px;
    background: var(--vert-nuit);
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 1rem;
    font-family: 'Playfair Display', serif;
    font-size: 1.8rem;
    color: var(--or);
}
.profil-nom {
    font-family: 'Playfair Display', serif;
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--vert-nuit);
    margin-bottom: 0.2rem;
}
.profil-email { font-size: 0.78rem; color: #6a7f6a; margin-bottom: 1rem; }
.profil-stat {
    display: flex;
    justify-content: space-between;
    font-size: 0.8rem;
    color: #6a7f6a;
    padding: 0.6rem 0;
    border-top: 1px solid rgba(45,74,45,0.1);
}
.profil-stat strong { color: var(--vert-nuit); }
.tirage-badge {
    background: var(--or);
    color: var(--vert-nuit);
    padding: 8px 14px;
    border-radius: 2px;
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    margin-top: 1rem;
    display: block;
}
.historique-titre {
    font-family: 'Playfair Display', serif;
    font-size: 1.4rem;
    font-weight: 700;
    color: var(--vert-nuit);
    margin-bottom: 1.5rem;
}
.ticket-card {
    background: #fff;
    border: 1px solid rgba(45,74,45,0.1);
    border-radius: 4px;
    padding: 1.2rem 1.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 10px;
    transition: box-shadow 0.2s;
}
.ticket-card:hover { box-shadow: 0 4px 16px rgba(26,46,26,0.08); }
.ticket-icon {
    width: 44px; height: 44px;
    border-radius: 4px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.3rem;
    flex-shrink: 0;
}
.ticket-nom { font-weight: 600; font-size: 0.9rem; color: var(--vert-nuit); }
.ticket-meta { font-size: 0.75rem; color: #9aaa9a; margin-top: 2px; }
.ticket-badge {
    margin-left: auto;
    font-size: 0.68rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    padding: 4px 10px;
    border-radius: 2px;
}
.badge-remis { background: rgba(45,74,45,0.1); color: var(--vert-profond); }
.badge-attente { background: rgba(201,168,76,0.15); color: #8B6914; }
.empty-state {
    text-align: center;
    padding: 3rem 2rem;
    background: #fff;
    border: 1px solid rgba(45,74,45,0.1);
    border-radius: 4px;
}
.empty-state .empty-icon { font-size: 2.5rem; margin-bottom: 1rem; opacity: 0.4; }
.empty-state p { font-size: 0.88rem; color: #6a7f6a; margin-bottom: 1.5rem; }
@media (max-width: 768px) {
    .compte-grid { grid-template-columns: 1fr; }
}
</style>

<section class="compte-section">
    <div class="compte-grid">
        <div class="profil-card">
            <div class="profil-avatar">
                <?= strtoupper(substr($user['prenom'], 0, 1)) ?>
            </div>
            <div class="profil-nom"><?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></div>
            <div class="profil-email"><?= htmlspecialchars($user['email']) ?></div>
            <div class="profil-stat">
                <span>Codes utilisés</span>
                <strong><?= count($tickets) ?></strong>
            </div>
            <div class="profil-stat">
                <span>Membre depuis</span>
                <strong><?= date('M Y', strtotime($user['created_at'])) ?></strong>
            </div>
            <?php if ($tirage): ?>
                <span class="tirage-badge">✦ Inscrit au tirage final</span>
            <?php endif; ?>
            <div style="margin-top: 1.2rem;">
                <a href="/pages/participation.php" class="btn-primary-ttt"
                   style="display:block; text-align:center; font-size:0.75rem; padding: 10px;">
                    + Entrer un code
                </a>
            </div>
        </div>

        <div>
            <div class="historique-titre">Mes gains</div>
            <?php if (empty($tickets)): ?>
                <div class="empty-state">
                    <div class="empty-icon">🎟️</div>
                    <p>Vous n'avez pas encore utilisé de code.<br>
                    Entrez votre code de ticket de caisse pour découvrir votre gain.</p>
                    <a href="/pages/participation.php" class="btn-primary-ttt">Entrer mon premier code</a>
                </div>
            <?php else: ?>
                <?php foreach($tickets as $t):
                    $g = $gains_labels[$t['gain']] ?? ['label' => $t['gain'], 'icon' => '🎁', 'color' => '#2D4A2D'];
                ?>
                <div class="ticket-card">
                    <div class="ticket-icon" style="background: <?= $g['color'] ?>22;">
                        <?= $g['icon'] ?>
                    </div>
                    <div>
                        <div class="ticket-nom"><?= htmlspecialchars($g['label']) ?></div>
                        <div class="ticket-meta">
                            Code : <code><?= htmlspecialchars($t['code']) ?></code>
                            · <?= date('d/m/Y à H:i', strtotime($t['date_utilisation'])) ?>
                        </div>
                    </div>
                    <?php if ($t['remis']): ?>
                        <span class="ticket-badge badge-remis">Remis</span>
                    <?php else: ?>
                        <span class="ticket-badge badge-attente">À réclamer</span>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>