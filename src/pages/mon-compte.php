<?php
$page_title = 'Mon Compte — Thé Tip Top';
require_once __DIR__ . '/../includes/header.php';

if (!$user) { header('Location: /pages/connexion.php'); exit; }

$db = getDB();
$stmt = $db->prepare("SELECT * FROM tickets WHERE user_id = ? ORDER BY date_utilisation DESC");
$stmt->execute([$user['id']]);
$tickets = $stmt->fetchAll();

$gains_labels = [
    'infuseur'      => ['label' => 'Infuseur à thé', 'icon' => 'bi-cup-hot', 'color' => '#1B4332'],
    'the_detox'     => ['label' => 'Thé détox 100g', 'icon' => 'bi-stars', 'color' => '#2D6A4F'],
    'the_signature' => ['label' => 'Thé signature 100g', 'icon' => 'bi-award', 'color' => '#B5860D'],
    'coffret_39'    => ['label' => 'Coffret 39€', 'icon' => 'bi-gift', 'color' => '#E76F51'],
    'coffret_69'    => ['label' => 'Coffret 69€', 'icon' => 'bi-gift-fill', 'color' => '#E63946'],
];

$stmt2 = $db->prepare("SELECT * FROM tirage_final WHERE user_id = ?");
$stmt2->execute([$user['id']]);
$tirage = $stmt2->fetch();
?>

<main class="py-5">
    <div class="container">
        <div class="row g-4">
            <!-- Profil -->
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-3 p-4 text-center">
                    <div class="mb-3">
                        <div style="width:80px;height:80px;background:#1B4332;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto;font-size:2rem;color:white;">
                            <?= strtoupper(substr($user['prenom'], 0, 1)) ?>
                        </div>
                    </div>
                    <h5 class="fw-bold"><?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></h5>
                    <p class="text-muted small"><?= htmlspecialchars($user['email']) ?></p>
                    <hr>
                    <div class="d-flex justify-content-between small text-muted">
                        <span>Codes utilisés</span>
                        <strong><?= count($tickets) ?></strong>
                    </div>
                    <?php if ($tirage): ?>
                    <div class="alert alert-success small mt-3 mb-0">
                        <i class="bi bi-trophy me-1"></i>Inscrit au tirage final !
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Historique -->
            <div class="col-md-8">
                <h4 class="fw-bold mb-4" style="color:#1B4332;">
                    <i class="bi bi-clock-history me-2"></i>Historique de mes gains
                </h4>

                <?php if (empty($tickets)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-inbox fs-1 text-muted"></i>
                        <p class="text-muted mt-3">Vous n'avez pas encore utilisé de code.</p>
                        <a href="/pages/participation.php" class="btn btn-tiptop">
                            <i class="bi bi-upc-scan me-2"></i>Entrer mon premier code
                        </a>
                    </div>
                <?php else: ?>
                    <div class="row g-3">
                        <?php foreach($tickets as $t):
                            $g = $gains_labels[$t['gain']];
                        ?>
                        <div class="col-12">
                            <div class="card border-0 shadow-sm rounded-3 p-3 d-flex flex-row align-items-center gap-3">
                                <div style="width:50px;height:50px;background:<?= $g['color'] ?>;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                    <i class="bi <?= $g['icon'] ?> text-white fs-4"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-semibold"><?= $g['label'] ?></div>
                                    <div class="small text-muted">
                                        Code : <code><?= htmlspecialchars($t['code']) ?></code> —
                                        <?= date('d/m/Y H:i', strtotime($t['date_utilisation'])) ?>
                                    </div>
                                </div>
                                <div>
                                    <?php if ($t['remis']): ?>
                                        <span class="badge bg-success">Remis</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark">À réclamer</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="text-center mt-4">
                        <a href="/pages/participation.php" class="btn btn-tiptop">
                            <i class="bi bi-plus-circle me-2"></i>Entrer un nouveau code
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
