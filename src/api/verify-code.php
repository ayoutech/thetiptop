<?php
/**
 * src/api/verify-code.php
 * Vérifie l'existence et la validité d'un code, SANS le consommer.
 * Sécurisé par HTTPS + signature HMAC + anti-rejeu + rate limiting
 * (voir security.php).
 */

require_once __DIR__ . '/security.php';
apply_cors();
$keyRow = verify_signed_request();

require_once __DIR__ . '/../config/database.php';
$db = getDB();

$code = trim($_GET['code'] ?? $_POST['code'] ?? '');

if (!preg_match('/^[A-Z0-9]{10}$/', $code)) {
    http_response_code(400);
    echo json_encode(['error' => 'Format de code invalide']);
    exit;
}

$stmt = $db->prepare("SELECT code, gain, utilise FROM tickets WHERE code = ?");
$stmt->execute([$code]);
$ticket = $stmt->fetch();

if (!$ticket) {
    http_response_code(404);
    echo json_encode(['valid' => false]);
    exit;
}

echo json_encode([
    'valid'   => true,
    'utilise' => (bool)$ticket['utilise'],
    // le gain n'est renvoyé que si le code n'a pas encore été utilisé,
    // pour éviter de révéler des informations inutiles via l'API
    'gain'    => $ticket['utilise'] ? null : $ticket['gain'],
]);