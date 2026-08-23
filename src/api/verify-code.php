<?php
/**
 * API REST — Thé Tip Top
 * Endpoint : GET /api/verify-code.php?code=XXXXXXXXXX
 *
 * Démonstration de la séparation API / WebApp exigée au cahier des charges.
 * Cet endpoint est conçu pour être appelé par les caisses en magasin ou par
 * un futur front-end découplé (app mobile, site e-commerce, etc.).
 *
 * Réponse JSON, aucune session ni cookie requis — authentification par
 * clé API pour un usage en production (voir header X-Api-Key ci-dessous).
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

require_once __DIR__ . '/../config/database.php';

// Authentification simple par clé API (à stocker en variable d'environnement en prod)
$api_key_attendue = getenv('API_KEY') ?: 'ttt_demo_key_2026';
$api_key_recue = $_SERVER['HTTP_X_API_KEY'] ?? ($_GET['api_key'] ?? '');

if ($api_key_recue !== $api_key_attendue) {
    http_response_code(401);
    echo json_encode(['error' => 'Clé API invalide ou manquante'], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Méthode non autorisée, utilisez GET'], JSON_UNESCAPED_UNICODE);
    exit;
}

$code = strtoupper(trim($_GET['code'] ?? ''));

if (strlen($code) !== 10 || !preg_match('/^[A-Z0-9]{10}$/', $code)) {
    http_response_code(400);
    echo json_encode(['error' => 'Format de code invalide, 10 caractères alphanumériques attendus'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $db = getDB();
    $stmt = $db->prepare("SELECT code, gain, utilise, date_utilisation FROM tickets WHERE code = ?");
    $stmt->execute([$code]);
    $ticket = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$ticket) {
        http_response_code(404);
        echo json_encode(['error' => 'Code introuvable', 'code' => $code], JSON_UNESCAPED_UNICODE);
        exit;
    }

    echo json_encode([
        'code'    => $ticket['code'],
        'valide'  => true,
        'utilise' => (bool) $ticket['utilise'],
        'gain'    => $ticket['gain'],
        'date_utilisation' => $ticket['date_utilisation'],
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur serveur'], JSON_UNESCAPED_UNICODE);
}
