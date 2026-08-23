<?php
/**
 * API REST — Thé Tip Top
 * Endpoint : GET /api/stats.php
 *
 * Statistiques publiques du jeu-concours, consommables par un futur
 * site e-commerce ou une application mobile sans accès à la base de données.
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Méthode non autorisée, utilisez GET'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $db = getDB();

    $total_codes    = (int) $db->query("SELECT COUNT(*) FROM tickets")->fetchColumn();
    $codes_utilises = (int) $db->query("SELECT COUNT(*) FROM tickets WHERE utilise = 1")->fetchColumn();
    $participants   = (int) $db->query("SELECT COUNT(*) FROM tirage_final")->fetchColumn();

    echo json_encode([
        'jeu' => 'Thé Tip Top — Jeu-concours 100% gagnant',
        'codes_total'      => $total_codes,
        'codes_utilises'   => $codes_utilises,
        'codes_restants'   => $total_codes - $codes_utilises,
        'taux_utilisation' => $total_codes > 0 ? round($codes_utilises / $total_codes * 100, 2) : 0,
        'participants_tirage_final' => $participants,
        'genere_le' => date('c'),
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur serveur'], JSON_UNESCAPED_UNICODE);
}
