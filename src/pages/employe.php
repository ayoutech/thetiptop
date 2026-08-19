<?php ob_start();
$page_title = 'Espace Boutique — Thé Tip Top';
require_once __DIR__ . '/../includes/header.php';

if (!$user || !in_array($user['role'], ['employe','admin'])) {
    header('Location: /'); exit;
}

$db = getDB();
$client = null;
$tickets_client = [];
$error = '';
$success = '';
$gains_labels = [
    'infuseur'      => 'Infuseur à thé',
    'the_detox'     => 'Thé détox 100g',
    'the_signature' => 'Thé signature 100g',
    'coffret_39'    => 'Coffret découverte 39€',
    'coffret_69'    => 'Coffret découverte 69€',
];

// Marquer comme remis
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['marquer_remis'])) {
    $ticket_id = intval($_POST['ticket_id']);
    $stmt = $db->prepare("UPDATE tickets SET remis = 1 WHERE id = ? AND utilise = 1");
    $stmt->execute([$ticket_id]);
    $success = 'Le lot a été marqué comme remis.';
}

// Recherche client
if (isset($_GET['recherche'])) {
    $recherche = trim($_GET['recherche']);
    $stmt = $db->prepare("SELECT * FROM users WHERE email = ? OR id = ?");
    $stmt->execute([$recherche, intval($recherche)]);
    $client = $stmt->fetch();
    if ($client) {
        $stmt2 = $db->prepare("SELECT * FROM tickets WHERE user_id = ? AND utilise = 1 ORDER BY date_utilisation DESC");
        $stmt2->execute([$client['id']]);
        $tickets_client = $stmt2->fetchAll();
    } else {
        $error = 'Aucun client trouvé avec ces informations.';
    }
}
?>

<main class="py-5">
<div class="container">
    <h3 class="fw-bold mb-4" style="color:#1B4332;">
        <i class="bi bi-shop me-2"></i>Espace Employé Boutique
    </h3>

    <!-- Recherche -->
    <div class="card border-0 shadow-sm rounded-3 p-4 mb-4" style="max-width:500px;">
        <h5 class="fw-bold mb-3">Rechercher un client</h5>
        <form method="GET">
            <div class="input-group">
                <input type="text" name="recherche" class="form-control"
                       placeholder="Email du client"
                       value="<?= htmlspecialchars($_GET['recherche'] ?? '') ?>">
                <button type="submit" class="btn btn-tiptop">
                    <i class="bi bi-search me-1"></i>Rechercher
                </button>
            </div>
        </form>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <?php if ($client): ?>
        <!-- Infos client -->
        <div class="card border-0 shadow-sm rounded-3 p-4 mb-4">
            <div class="d-flex align-items-center gap-3 mb-3">
                <div style="width:55px;height:55px;background:#1B4332;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.5rem;color:white;">
                    <?= strtoupper(substr($client['prenom'], 0, 1)) ?>
                </div>
                <div>
                    <h5 class="mb-0 fw-bold"><?= htmlspecialchars($client['prenom'] . ' ' . $client['nom']) ?></h5>
                    <p class="mb-0 text-muted small"><?= htmlspecialchars($client['email']) ?></p>
                </div>
            </div>

            <?php if (empty($tickets_client)): ?>
                <p class="text-muted">Ce client n'a pas encore utilisé de code.</p>
            <?php else: ?>
                <h6 class="fw-bold mb-3">Gains du client (<?= count($tickets_client) ?> code(s) utilisé(s))</h6>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-admin">
                            <tr><th>Code</th><th>Gain</th><th>Date</th><th>Statut</th><th>Action</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach($tickets_client as $t): ?>
                            <tr>
                                <td><code><?= htmlspecialchars($t['code']) ?></code></td>
                                <td><?= $gains_labels[$t['gain']] ?? $t['gain'] ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($t['date_utilisation'])) ?></td>
                                <td>
                                    <?php if ($t['remis']): ?>
                                        <span class="badge bg-success">Remis</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark">À remettre</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!$t['remis']): ?>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="ticket_id" value="<?= $t['id'] ?>">
                                        <input type="hidden" name="marquer_remis" value="1">
                                        <button type="submit" class="btn btn-sm btn-success"
                                                onclick="return confirmerAction('Confirmer la remise du lot ?')">
                                            <i class="bi bi-check2 me-1"></i>Marquer remis
                                        </button>
                                    </form>
                                    <?php else: ?>
                                        <span class="text-muted small">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
