<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$page_title = 'Espace Boutique — Thé Tip Top';
require_once __DIR__ . '/../includes/header.php';

if (!$user || !in_array($user['role'], ['employe', 'admin'])) {
    header('Location: /'); exit;
}

$db = getDB();
$client = null;
$tickets_client = [];
$error = '';
$success = '';
$gains_labels = [
    'infuseur'      => ['label' => 'Infuseur à thé',          'icon' => '🍵'],
    'the_detox'     => ['label' => 'Thé détox 100g',          'icon' => '🌿'],
    'the_signature' => ['label' => 'Thé signature 100g',      'icon' => '⭐'],
    'coffret_39'    => ['label' => 'Coffret découverte 39€',  'icon' => '🎁'],
    'coffret_69'    => ['label' => 'Coffret découverte 69€',  'icon' => '✨'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['marquer_remis'])) {
    $ticket_id = intval($_POST['ticket_id']);
    $stmt = $db->prepare("UPDATE tickets SET remis = 1 WHERE id = ? AND utilise = 1");
    $stmt->execute([$ticket_id]);
    $success = 'Le lot a été marqué comme remis.';
}

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

<style>
.employe-page {
    background: var(--blanc-casse);
    min-height: calc(100vh - 68px);
    padding: 2.5rem 2rem;
}
.employe-title {
    font-family: 'Playfair Display', serif;
    font-size: 1.8rem;
    font-weight: 700;
    color: var(--vert-nuit);
    margin-bottom: 0.3rem;
}
.employe-sub {
    font-size: 0.82rem;
    color: #6a7f6a;
    margin-bottom: 2rem;
}
.employe-card {
    background: #fff;
    border: 1px solid rgba(45,74,45,0.1);
    border-radius: 4px;
    padding: 1.8rem;
    box-shadow: 0 2px 12px rgba(26,46,26,0.04);
    margin-bottom: 1.5rem;
}
.employe-card-title {
    font-family: 'Playfair Display', serif;
    font-size: 1rem;
    font-weight: 700;
    color: var(--vert-nuit);
    margin-bottom: 1rem;
    padding-bottom: 0.6rem;
    border-bottom: 1px solid rgba(201,168,76,0.2);
}
.search-row {
    display: flex;
    gap: 10px;
    max-width: 500px;
}
.search-input {
    flex: 1;
    padding: 11px 14px;
    border: 1px solid rgba(45,74,45,0.2);
    border-radius: 2px;
    font-size: 0.88rem;
    font-family: 'Inter', sans-serif;
    color: var(--vert-nuit);
    outline: none;
    transition: border-color 0.2s, box-shadow 0.2s;
}
.search-input:focus {
    border-color: var(--or);
    box-shadow: 0 0 0 3px rgba(201,168,76,0.12);
}
.search-input::placeholder { color: #b0c0b0; }
.btn-search {
    background: var(--vert-nuit);
    color: var(--creme);
    padding: 11px 22px;
    border: none;
    border-radius: 2px;
    font-size: 0.8rem;
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    cursor: pointer;
    transition: background 0.2s;
    white-space: nowrap;
}
.btn-search:hover { background: var(--or); color: var(--vert-nuit); }

.alert-ttt {
    padding: 12px 16px;
    border-radius: 2px;
    font-size: 0.82rem;
    margin-bottom: 1rem;
}
.alert-erreur { background: #fdf0f0; border: 1px solid #f0c0c0; color: #8b2020; }
.alert-ok { background: #f0fdf4; border: 1px solid #c0e0c8; color: #1a4a2a; }

.client-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1.5rem;
}
.client-avatar {
    width: 52px; height: 52px;
    background: var(--vert-nuit);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'Playfair Display', serif;
    font-size: 1.4rem;
    color: var(--or);
    flex-shrink: 0;
}
.client-nom {
    font-family: 'Playfair Display', serif;
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--vert-nuit);
}
.client-email { font-size: 0.8rem; color: #6a7f6a; margin-top: 2px; }

.employe-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.82rem;
}
.employe-table th {
    font-size: 0.68rem;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: #6a7f6a;
    font-weight: 600;
    padding: 8px 10px;
    border-bottom: 1px solid rgba(45,74,45,0.1);
    text-align: left;
}
.employe-table td {
    padding: 10px;
    border-bottom: 1px solid rgba(45,74,45,0.06);
    color: var(--vert-nuit);
    vertical-align: middle;
}
.employe-table tr:last-child td { border-bottom: none; }
.employe-table tr:hover td { background: #f8f3e7; }
.employe-table code {
    font-size: 0.75rem;
    background: rgba(45,74,45,0.06);
    padding: 2px 8px;
    border-radius: 2px;
    color: var(--vert-profond);
    letter-spacing: 0.05em;
}
.badge-remis {
    background: rgba(45,74,45,0.1);
    color: var(--vert-profond);
    padding: 3px 10px;
    border-radius: 2px;
    font-size: 0.68rem;
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
}
.badge-attente {
    background: rgba(201,168,76,0.15);
    color: #8B6914;
    padding: 3px 10px;
    border-radius: 2px;
    font-size: 0.68rem;
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
}
.btn-remis {
    background: var(--vert-nuit);
    color: var(--creme);
    padding: 6px 14px;
    border: none;
    border-radius: 2px;
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    cursor: pointer;
    transition: background 0.2s;
}
.btn-remis:hover { background: var(--or); color: var(--vert-nuit); }
.empty-state {
    text-align: center;
    padding: 2rem;
    font-size: 0.88rem;
    color: #6a7f6a;
}
</style>

<div class="employe-page">
    <div class="employe-title">Espace Employé Boutique</div>
    <div class="employe-sub">Recherchez un client et gérez la remise de ses lots</div>

    <!-- Recherche -->
    <div class="employe-card" style="max-width: 560px;">
        <div class="employe-card-title">🔍 Rechercher un client</div>
        <form method="GET">
            <div class="search-row">
                <input type="text" name="recherche" class="search-input"
                       placeholder="Email du client"
                       value="<?= htmlspecialchars($_GET['recherche'] ?? '') ?>">
                <button type="submit" class="btn-search">Rechercher</button>
            </div>
        </form>
    </div>

    <?php if ($error): ?>
        <div class="alert-ttt alert-erreur"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert-ttt alert-ok">✅ <?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <?php if ($client): ?>
    <div class="employe-card">
        <div class="client-header">
            <div class="client-avatar">
                <?= strtoupper(substr($client['prenom'], 0, 1)) ?>
            </div>
            <div>
                <div class="client-nom"><?= htmlspecialchars($client['prenom'] . ' ' . $client['nom']) ?></div>
                <div class="client-email"><?= htmlspecialchars($client['email']) ?></div>
            </div>
        </div>

        <?php if (empty($tickets_client)): ?>
            <div class="empty-state">Ce client n'a pas encore utilisé de code.</div>
        <?php else: ?>
            <div class="employe-card-title">
                🎁 Gains du client — <?= count($tickets_client) ?> code(s) utilisé(s)
            </div>
            <div style="overflow-x: auto;">
                <table class="employe-table">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Gain</th>
                            <th>Date</th>
                            <th>Statut</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($tickets_client as $t):
                            $g = $gains_labels[$t['gain']] ?? ['label' => $t['gain'], 'icon' => '🎁'];
                        ?>
                        <tr>
                            <td><code><?= htmlspecialchars($t['code']) ?></code></td>
                            <td><?= $g['icon'] ?> <?= $g['label'] ?></td>
                            <td style="color:#6a7f6a;"><?= date('d/m/Y H:i', strtotime($t['date_utilisation'])) ?></td>
                            <td>
                                <?php if ($t['remis']): ?>
                                    <span class="badge-remis">Remis</span>
                                <?php else: ?>
                                    <span class="badge-attente">À remettre</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!$t['remis']): ?>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="ticket_id" value="<?= $t['id'] ?>">
                                    <input type="hidden" name="marquer_remis" value="1">
                                    <button type="submit" class="btn-remis"
                                            onclick="return confirm('Confirmer la remise du lot ?')">
                                        ✓ Marquer remis
                                    </button>
                                </form>
                                <?php else: ?>
                                    <span style="color:#9aaa9a; font-size:0.78rem;">—</span>
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

<?php require_once __DIR__ . '/../includes/footer.php'; ?>