<?php
/**
 * src/api/stats.php
 * Statistiques publiques du jeu-concours (agrégées, non nominatives).
 * Sécurisé par HTTPS + signature HMAC + anti-rejeu + rate limiting.
 */

require_once __DIR__ . '/security.php';
apply_cors();
$keyRow = verify_signed_request();

require_once __DIR__ . '/../config/database.php';
$db = getDB();

$total = (int)$db->query("SELECT COUNT(*) FROM tickets")->fetchColumn();
$utilises = (int)$db->query("SELECT COUNT(*) FROM tickets WHERE utilise = 1")->fetchColumn();

echo json_encode([
    'tickets_total'     => $total,
    'tickets_utilises'  => $utilises,
    'taux_participation'=> $total > 0 ? round($utilises / $total * 100, 2) : 0,
]);
