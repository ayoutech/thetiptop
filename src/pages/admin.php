<?php ob_start();
$page_title = 'Administration — Thé Tip Top';
require_once __DIR__ . '/../includes/header.php';

if (!$user || $user['role'] !== 'admin') {
    header('Location: /'); exit;
}

$db = getDB();

// Stats globales
$total_codes    = $db->query("SELECT COUNT(*) FROM tickets")->fetchColumn();
$codes_utilises = $db->query("SELECT COUNT(*) FROM tickets WHERE utilise = 1")->fetchColumn();
$lots_remis     = $db->query("SELECT COUNT(*) FROM tickets WHERE remis = 1")->fetchColumn();
$total_users    = $db->query("SELECT COUNT(*) FROM users WHERE role = 'client'")->fetchColumn();
$participants   = $db->query("SELECT COUNT(*) FROM tirage_final")->fetchColumn();

// Répartition par gain
$repartition = $db->query("SELECT gain, COUNT(*) as total, SUM(utilise) as utilises FROM tickets GROUP BY gain")->fetchAll();

// Stats par sexe
$par_sexe = $db->query("
    SELECT u.sexe, COUNT(DISTINCT t.user_id) as nb
    FROM tickets t
    JOIN users u ON t.user_id = u.id
    WHERE t.utilise = 1
    GROUP BY u.sexe
")->fetchAll();

// Stats par tranche d'âge
$par_age = $db->query("
    SELECT
        CASE
            WHEN u.age < 25 THEN '18-24 ans'
            WHEN u.age < 35 THEN '25-34 ans'
            WHEN u.age < 45 THEN '35-44 ans'
            WHEN u.age < 55 THEN '45-54 ans'
            ELSE '55 ans et +'
        END as tranche,
        COUNT(DISTINCT t.user_id) as nb
    FROM tickets t
    JOIN users u ON t.user_id = u.id
    WHERE t.utilise = 1
    GROUP BY tranche ORDER BY tranche
")->fetchAll();

// Derniers participants
$derniers = $db->query("
    SELECT u.prenom, u.nom, u.email, t.gain, t.code, t.date_utilisation, t.remis
    FROM tickets t JOIN users u ON t.user_id = u.id
    WHERE t.utilise = 1
    ORDER BY t.date_utilisation DESC LIMIT 20
")->fetchAll();

// Export CSV emails
if (isset($_GET['export_emails'])) {
    $emails = $db->query("SELECT email, nom, prenom FROM users WHERE newsletter = 1 AND role = 'client'")->fetchAll();
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=emails_newsletter.csv');
    echo "Email,Nom,Prenom\n";
    foreach ($emails as $e) {
        echo $e['email'] . ',' . $e['nom'] . ',' . $e['prenom'] . "\n";
    }
    exit;
}

$gains_labels = ['infuseur'=>'Infuseur à thé','the_detox'=>'Thé détox 100g','the_signature'=>'Thé signature 100g','coffret_39'=>'Coffret 39€','coffret_69'=>'Coffret 69€'];
?>

<main class="py-4">
<div class="container-fluid">
    <h3 class="fw-bold mb-4" style="color:#1B4332;">
        <i class="bi bi-speedometer2 me-2"></i>Tableau de bord administrateur
    </h3>

    <!-- Stats globales -->
    <div class="row g-3 mb-4">
        <?php
        $stats = [
            ['label'=>'Codes générés','val'=>number_format($total_codes),'icon'=>'bi-ticket-perforated','color'=>'#1B4332'],
            ['label'=>'Codes utilisés','val'=>number_format($codes_utilises),'icon'=>'bi-check-circle','color'=>'#2D6A4F'],
            ['label'=>'Lots remis','val'=>number_format($lots_remis),'icon'=>'bi-gift','color'=>'#B5860D'],
            ['label'=>'Participants inscrits','val'=>number_format($total_users),'icon'=>'bi-people','color'=>'#264653'],
            ['label'=>'Au tirage final','val'=>number_format($participants),'icon'=>'bi-trophy','color'=>'#E76F51'],
        ];
        foreach($stats as $s): ?>
        <div class="col-md-2 col-6">
            <div class="stat-card">
                <i class="bi <?= $s['icon'] ?> fs-2 mb-2" style="color:<?= $s['color'] ?>;"></i>
                <div class="stat-number" style="color:<?= $s['color'] ?>;"><?= $s['val'] ?></div>
                <div class="stat-label"><?= $s['label'] ?></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="row g-4">
        <!-- Répartition gains -->
        <div class="col-md-5">
            <div class="card border-0 shadow-sm rounded-3 p-4">
                <h5 class="fw-bold mb-3" style="color:#1B4332;">Répartition des gains</h5>
                <table class="table table-sm">
                    <thead class="table-admin"><tr><th>Gain</th><th>Total</th><th>Utilisés</th><th>Taux</th></tr></thead>
                    <tbody>
                        <?php foreach($repartition as $r): ?>
                        <tr>
                            <td><?= $gains_labels[$r['gain']] ?? $r['gain'] ?></td>
                            <td><?= number_format($r['total']) ?></td>
                            <td><?= number_format($r['utilises']) ?></td>
                            <td><?= $r['total'] > 0 ? round($r['utilises'] / $r['total'] * 100, 1) : 0 ?>%</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Stats démographiques -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-4 mb-3">
                <h5 class="fw-bold mb-3" style="color:#1B4332;">Par sexe</h5>
                <?php foreach($par_sexe as $s): ?>
                <div class="d-flex justify-content-between mb-1 small">
                    <span><?= ucfirst($s['sexe']) ?></span>
                    <strong><?= $s['nb'] ?></strong>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="card border-0 shadow-sm rounded-3 p-4">
                <h5 class="fw-bold mb-3" style="color:#1B4332;">Par âge</h5>
                <?php foreach($par_age as $a): ?>
                <div class="d-flex justify-content-between mb-1 small">
                    <span><?= $a['tranche'] ?></span>
                    <strong><?= $a['nb'] ?></strong>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Export emails -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 p-4">
                <h5 class="fw-bold mb-3" style="color:#1B4332;">Export emails</h5>
                <p class="small text-muted">Exporter les emails des utilisateurs ayant accepté la newsletter pour vos campagnes emailing.</p>
                <a href="?export_emails=1" class="btn btn-tiptop w-100">
                    <i class="bi bi-download me-2"></i>Télécharger CSV emails
                </a>
            </div>
        </div>
    </div>

    <!-- Derniers participants -->
    <div class="card border-0 shadow-sm rounded-3 p-4 mt-4">
        <h5 class="fw-bold mb-3" style="color:#1B4332;">20 dernières participations</h5>
        <div class="table-responsive">
            <table class="table table-sm table-hover">
                <thead class="table-admin">
                    <tr><th>Nom</th><th>Email</th><th>Code</th><th>Gain</th><th>Date</th><th>Remis</th></tr>
                </thead>
                <tbody>
                    <?php foreach($derniers as $d): ?>
                    <tr>
                        <td><?= htmlspecialchars($d['prenom'] . ' ' . $d['nom']) ?></td>
                        <td><?= htmlspecialchars($d['email']) ?></td>
                        <td><code><?= htmlspecialchars($d['code']) ?></code></td>
                        <td><?= $gains_labels[$d['gain']] ?? $d['gain'] ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($d['date_utilisation'])) ?></td>
                        <td><?= $d['remis'] ? '<span class="badge bg-success">Oui</span>' : '<span class="badge bg-warning text-dark">Non</span>' ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
