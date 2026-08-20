<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$page_title = 'Administration — Thé Tip Top';
require_once __DIR__ . '/../includes/header.php';

if (!$user || $user['role'] !== 'admin') {
    header('Location: /'); exit;
}

$db = getDB();

$total_codes    = $db->query("SELECT COUNT(*) FROM tickets")->fetchColumn();
$codes_utilises = $db->query("SELECT COUNT(*) FROM tickets WHERE utilise = 1")->fetchColumn();
$lots_remis     = $db->query("SELECT COUNT(*) FROM tickets WHERE remis = 1")->fetchColumn();
$total_users    = $db->query("SELECT COUNT(*) FROM users WHERE role = 'client'")->fetchColumn();
$participants   = $db->query("SELECT COUNT(*) FROM tirage_final")->fetchColumn();

$repartition = $db->query("SELECT gain, COUNT(*) as total, SUM(utilise) as utilises FROM tickets GROUP BY gain")->fetchAll();

$par_sexe = $db->query("
    SELECT u.sexe, COUNT(DISTINCT t.user_id) as nb
    FROM tickets t JOIN users u ON t.user_id = u.id
    WHERE t.utilise = 1 GROUP BY u.sexe
")->fetchAll();

$par_age = $db->query("
    SELECT CASE
        WHEN u.age < 25 THEN '18-24 ans'
        WHEN u.age < 35 THEN '25-34 ans'
        WHEN u.age < 45 THEN '35-44 ans'
        WHEN u.age < 55 THEN '45-54 ans'
        ELSE '55 ans et +'
    END as tranche, COUNT(DISTINCT t.user_id) as nb
    FROM tickets t JOIN users u ON t.user_id = u.id
    WHERE t.utilise = 1 GROUP BY tranche ORDER BY tranche
")->fetchAll();

$derniers = $db->query("
    SELECT u.prenom, u.nom, u.email, t.gain, t.code, t.date_utilisation, t.remis
    FROM tickets t JOIN users u ON t.user_id = u.id
    WHERE t.utilise = 1
    ORDER BY t.date_utilisation DESC LIMIT 20
")->fetchAll();

if (isset($_GET['export_emails'])) {
    $emails = $db->query("SELECT email, nom, prenom FROM users WHERE newsletter = 1 AND role = 'client'")->fetchAll();
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=emails_newsletter.csv');
    echo "Email,Nom,Prenom\n";
    foreach ($emails as $e) echo $e['email'].','.$e['nom'].','.$e['prenom']."\n";
    exit;
}

$gains_labels = [
    'infuseur'      => ['label' => 'Infuseur à thé',     'icon' => '🍵'],
    'the_detox'     => ['label' => 'Thé détox 100g',     'icon' => '🌿'],
    'the_signature' => ['label' => 'Thé signature 100g', 'icon' => '⭐'],
    'coffret_39'    => ['label' => 'Coffret 39€',        'icon' => '🎁'],
    'coffret_69'    => ['label' => 'Coffret 69€',        'icon' => '✨'],
];
?>

<style>
.admin-page {
    background: var(--blanc-casse);
    min-height: calc(100vh - 68px);
    padding: 2.5rem 2rem;
}
.admin-title {
    font-family: 'Playfair Display', serif;
    font-size: 1.8rem;
    font-weight: 700;
    color: var(--vert-nuit);
    margin-bottom: 0.3rem;
}
.admin-sub {
    font-size: 0.82rem;
    color: #6a7f6a;
    margin-bottom: 2rem;
}

/* STAT CARDS */
.stats-row {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 1px;
    background: rgba(45,74,45,0.1);
    border: 1px solid rgba(45,74,45,0.1);
    border-radius: 4px;
    margin-bottom: 2rem;
    overflow: hidden;
}
.stat-box {
    background: #fff;
    padding: 1.5rem 1rem;
    text-align: center;
    transition: background 0.2s;
}
.stat-box:hover { background: #f8f3e7; }
.stat-box-icon { font-size: 1.4rem; margin-bottom: 0.5rem; display: block; }
.stat-box-num {
    font-family: 'Playfair Display', serif;
    font-size: 1.8rem;
    font-weight: 700;
    color: var(--vert-nuit);
    line-height: 1;
    margin-bottom: 0.3rem;
}
.stat-box-num.or { color: var(--or); }
.stat-box-label {
    font-size: 0.7rem;
    color: #6a7f6a;
    letter-spacing: 0.06em;
    text-transform: uppercase;
}

/* GRID */
.admin-grid {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr;
    gap: 1.5rem;
    margin-bottom: 2rem;
}

/* CARDS */
.admin-card {
    background: #fff;
    border: 1px solid rgba(45,74,45,0.1);
    border-radius: 4px;
    padding: 1.5rem;
    box-shadow: 0 2px 12px rgba(26,46,26,0.04);
}
.admin-card-title {
    font-family: 'Playfair Display', serif;
    font-size: 1rem;
    font-weight: 700;
    color: var(--vert-nuit);
    margin-bottom: 1rem;
    padding-bottom: 0.6rem;
    border-bottom: 1px solid rgba(201,168,76,0.2);
    display: flex;
    align-items: center;
    gap: 8px;
}

/* TABLE */
.admin-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.82rem;
}
.admin-table th {
    font-size: 0.68rem;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: #6a7f6a;
    font-weight: 600;
    padding: 8px 10px;
    border-bottom: 1px solid rgba(45,74,45,0.1);
    text-align: left;
}
.admin-table td {
    padding: 9px 10px;
    border-bottom: 1px solid rgba(45,74,45,0.06);
    color: var(--vert-nuit);
    vertical-align: middle;
}
.admin-table tr:last-child td { border-bottom: none; }
.admin-table tr:hover td { background: #f8f3e7; }
.admin-table code {
    font-size: 0.75rem;
    background: rgba(45,74,45,0.06);
    padding: 2px 6px;
    border-radius: 2px;
    color: var(--vert-profond);
}

/* BADGE */
.badge-oui {
    background: rgba(45,74,45,0.1);
    color: var(--vert-profond);
    padding: 3px 10px;
    border-radius: 2px;
    font-size: 0.68rem;
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
}
.badge-non {
    background: rgba(201,168,76,0.15);
    color: #8B6914;
    padding: 3px 10px;
    border-radius: 2px;
    font-size: 0.68rem;
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
}

/* DEMO ROW */
.demo-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid rgba(45,74,45,0.06);
    font-size: 0.83rem;
}
.demo-row:last-child { border-bottom: none; }
.demo-row span { color: #6a7f6a; }
.demo-row strong { color: var(--vert-nuit); }

/* BTN */
.btn-admin {
    display: block;
    width: 100%;
    background: var(--vert-nuit);
    color: var(--creme);
    padding: 11px;
    border: none;
    border-radius: 2px;
    font-size: 0.78rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    cursor: pointer;
    text-align: center;
    text-decoration: none;
    transition: background 0.2s;
    margin-top: 1rem;
}
.btn-admin:hover { background: var(--or); color: var(--vert-nuit); }

/* TAUX BAR */
.taux-bar {
    display: flex;
    align-items: center;
    gap: 8px;
}
.taux-bg {
    flex: 1;
    height: 4px;
    background: rgba(45,74,45,0.08);
    border-radius: 2px;
    overflow: hidden;
}
.taux-fill {
    height: 100%;
    background: var(--or);
    border-radius: 2px;
    transition: width 0.5s;
}

@media (max-width: 1024px) {
    .stats-row { grid-template-columns: repeat(3, 1fr); }
    .admin-grid { grid-template-columns: 1fr; }
}
@media (max-width: 768px) {
    .stats-row { grid-template-columns: repeat(2, 1fr); }
    .admin-page { padding: 1.5rem 1rem; }
}
</style>

<div class="admin-page">
    <div class="admin-title">Tableau de bord</div>
    <div class="admin-sub">Administration Thé Tip Top — Jeu-Concours 100% Gagnant</div>

    <!-- STATS -->
    <div class="stats-row">
        <div class="stat-box">
            <span class="stat-box-icon">🎟️</span>
            <div class="stat-box-num"><?= number_format($total_codes) ?></div>
            <div class="stat-box-label">Codes générés</div>
        </div>
        <div class="stat-box">
            <span class="stat-box-icon">✅</span>
            <div class="stat-box-num or"><?= number_format($codes_utilises) ?></div>
            <div class="stat-box-label">Codes utilisés</div>
        </div>
        <div class="stat-box">
            <span class="stat-box-icon">🎁</span>
            <div class="stat-box-num"><?= number_format($lots_remis) ?></div>
            <div class="stat-box-label">Lots remis</div>
        </div>
        <div class="stat-box">
            <span class="stat-box-icon">👥</span>
            <div class="stat-box-num"><?= number_format($total_users) ?></div>
            <div class="stat-box-label">Participants inscrits</div>
        </div>
        <div class="stat-box">
            <span class="stat-box-icon">🏆</span>
            <div class="stat-box-num or"><?= number_format($participants) ?></div>
            <div class="stat-box-label">Au tirage final</div>
        </div>
    </div>

    <!-- GRID MILIEU -->
    <div class="admin-grid">
        <!-- Répartition gains -->
        <div class="admin-card">
            <div class="admin-card-title">📊 Répartition des gains</div>
            <table class="admin-table">
                <thead>
                    <tr><th>Gain</th><th>Total</th><th>Utilisés</th><th>Taux</th></tr>
                </thead>
                <tbody>
                    <?php foreach($repartition as $r):
                        $g = $gains_labels[$r['gain']] ?? ['label' => $r['gain'], 'icon' => '🎁'];
                        $taux = $r['total'] > 0 ? round($r['utilises'] / $r['total'] * 100, 1) : 0;
                    ?>
                    <tr>
                        <td><?= $g['icon'] ?> <?= $g['label'] ?></td>
                        <td><?= number_format($r['total']) ?></td>
                        <td><?= number_format($r['utilises']) ?></td>
                        <td>
                            <div class="taux-bar">
                                <div class="taux-bg"><div class="taux-fill" style="width:<?= min($taux * 10, 100) ?>%"></div></div>
                                <span style="font-size:0.75rem; color:#6a7f6a; white-space:nowrap;"><?= $taux ?>%</span>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Démographie -->
        <div class="admin-card">
            <div class="admin-card-title">👤 Par sexe</div>
            <?php foreach($par_sexe as $s): ?>
            <div class="demo-row">
                <span><?= ucfirst($s['sexe']) ?></span>
                <strong><?= $s['nb'] ?></strong>
            </div>
            <?php endforeach; ?>

            <div class="admin-card-title" style="margin-top: 1.5rem;">🎂 Par âge</div>
            <?php foreach($par_age as $a): ?>
            <div class="demo-row">
                <span><?= $a['tranche'] ?></span>
                <strong><?= $a['nb'] ?></strong>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Export -->
        <div class="admin-card">
            <div class="admin-card-title">📧 Export emails</div>
            <p style="font-size:0.82rem; color:#6a7f6a; line-height:1.6;">
                Exporter les emails des utilisateurs ayant accepté la newsletter pour vos campagnes emailing.
            </p>
            <a href="?export_emails=1" class="btn-admin">⬇ Télécharger CSV</a>
        </div>
    </div>

    <!-- DERNIÈRES PARTICIPATIONS -->
    <div class="admin-card">
        <div class="admin-card-title">🕐 20 dernières participations</div>
        <div style="overflow-x: auto;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Code</th>
                        <th>Gain</th>
                        <th>Date</th>
                        <th>Remis</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($derniers as $d):
                        $g = $gains_labels[$d['gain']] ?? ['label' => $d['gain'], 'icon' => '🎁'];
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($d['prenom'] . ' ' . $d['nom']) ?></td>
                        <td style="color:#6a7f6a;"><?= htmlspecialchars($d['email']) ?></td>
                        <td><code><?= htmlspecialchars($d['code']) ?></code></td>
                        <td><?= $g['icon'] ?> <?= $g['label'] ?></td>
                        <td style="color:#6a7f6a;"><?= date('d/m/Y H:i', strtotime($d['date_utilisation'])) ?></td>
                        <td>
                            <?php if($d['remis']): ?>
                                <span class="badge-oui">Remis</span>
                            <?php else: ?>
                                <span class="badge-non">En attente</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>