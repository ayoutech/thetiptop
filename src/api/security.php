<?php
/**
 * src/api/security.php
 *
 * Sécurisation des échanges entre l'application Thé Tip Top et son API REST.
 * Remplace la simple vérification de clé API statique par :
 *   - obligation HTTPS
 *   - signature HMAC-SHA256 de chaque requête (intégrité)
 *   - fenêtre anti-rejeu basée sur un horodatage
 *   - limitation de débit par clé API (rate limiting)
 *   - journalisation des tentatives d'authentification échouées
 *
 * Les clés API et secrets HMAC sont différents par environnement
 * (dev / preprod / prod) et sont lus depuis les variables d'environnement,
 * jamais codés en dur.
 */

require_once __DIR__ . '/../config/database.php';

// ---------------------------------------------------------------------
// Configuration (valeurs lues depuis l'environnement Render / Jenkins)
// ---------------------------------------------------------------------
define('API_HMAC_SECRET', getenv('API_HMAC_SECRET') ?: '');
define('API_MAX_TIMESTAMP_DRIFT', 300);   // 5 minutes, anti-rejeu
define('API_RATE_LIMIT_PER_MINUTE', 60);

/**
 * Point d'entrée à appeler en tête de chaque endpoint de l'API.
 * Coupe l'exécution (exit) et renvoie une réponse JSON d'erreur si la
 * requête n'est pas valide.
 */
function verify_signed_request(): array
{
    header('Content-Type: application/json');

    // 1. HTTPS obligatoire (Render place la valeur dans HTTP_X_FORWARDED_PROTO)
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    if (!$isHttps) {
        http_response_code(400);
        exit(json_encode(['error' => 'HTTPS requis']));
    }

    $apiKey    = $_SERVER['HTTP_X_API_KEY'] ?? '';
    $timestamp = $_SERVER['HTTP_X_TIMESTAMP'] ?? '';
    $signature = $_SERVER['HTTP_X_SIGNATURE'] ?? '';

    if ($apiKey === '' || $timestamp === '' || $signature === '') {
        http_response_code(401);
        exit(json_encode(['error' => 'En-têtes d\'authentification manquants']));
    }

    // 2. Fenêtre anti-rejeu
    if (!ctype_digit($timestamp) || abs(time() - (int)$timestamp) > API_MAX_TIMESTAMP_DRIFT) {
        log_failed_auth($apiKey, 'timestamp expiré ou invalide');
        http_response_code(401);
        exit(json_encode(['error' => 'Requête expirée']));
    }

    // 3. Limitation de débit
    if (!check_rate_limit($apiKey)) {
        http_response_code(429);
        exit(json_encode(['error' => 'Trop de requêtes, réessayez plus tard']));
    }

    // 4. Vérification de la clé API elle-même (existence + statut actif)
    $keyRow = lookup_api_key($apiKey);
    if (!$keyRow) {
        log_failed_auth($apiKey, 'clé API inconnue');
        http_response_code(401);
        exit(json_encode(['error' => 'Clé API invalide']));
    }

    // 5. Vérification de la signature HMAC
    $body = file_get_contents('php://input');
    $payload = $_SERVER['REQUEST_METHOD'] . '|' . $_SERVER['REQUEST_URI'] . '|' . $timestamp . '|' . $body;
    $expected = hash_hmac('sha256', $payload, API_HMAC_SECRET);

    if (!hash_equals($expected, $signature)) {
        log_failed_auth($apiKey, 'signature invalide');
        http_response_code(401);
        exit(json_encode(['error' => 'Signature invalide']));
    }

    return $keyRow;
}

/**
 * Limitation de débit simple, basée sur une table SQL (pas besoin de Redis
 * pour le volume de ce projet). Fenêtre glissante d'une minute.
 */
function check_rate_limit(string $apiKey): bool
{
    $db = getDB();
    $db->exec("CREATE TABLE IF NOT EXISTS api_rate_limit (
        api_key VARCHAR(64) NOT NULL,
        window_start DATETIME NOT NULL,
        request_count INT NOT NULL DEFAULT 0,
        PRIMARY KEY (api_key, window_start)
    )");

    $windowStart = date('Y-m-d H:i:00'); // minute en cours

    $stmt = $db->prepare(
        "INSERT INTO api_rate_limit (api_key, window_start, request_count)
         VALUES (?, ?, 1)
         ON DUPLICATE KEY UPDATE request_count = request_count + 1"
    );
    $stmt->execute([$apiKey, $windowStart]);

    $stmt = $db->prepare(
        "SELECT request_count FROM api_rate_limit WHERE api_key = ? AND window_start = ?"
    );
    $stmt->execute([$apiKey, $windowStart]);
    $count = (int)$stmt->fetchColumn();

    return $count <= API_RATE_LIMIT_PER_MINUTE;
}

function lookup_api_key(string $apiKey): ?array
{
    $db = getDB();
    $db->exec("CREATE TABLE IF NOT EXISTS api_keys (
        api_key VARCHAR(64) PRIMARY KEY,
        label VARCHAR(100) NOT NULL,
        environment VARCHAR(20) NOT NULL DEFAULT 'prod',
        active TINYINT(1) NOT NULL DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    $stmt = $db->prepare("SELECT * FROM api_keys WHERE api_key = ? AND active = 1");
    $stmt->execute([$apiKey]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function log_failed_auth(string $apiKey, string $reason): void
{
    $db = getDB();
    $db->exec("CREATE TABLE IF NOT EXISTS api_auth_failures (
        id INT AUTO_INCREMENT PRIMARY KEY,
        api_key VARCHAR(64),
        ip_address VARCHAR(45),
        reason VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    $stmt = $db->prepare(
        "INSERT INTO api_auth_failures (api_key, ip_address, reason) VALUES (?, ?, ?)"
    );
    $stmt->execute([$apiKey, $_SERVER['REMOTE_ADDR'] ?? 'unknown', $reason]);
}

/**
 * CORS restreint aux origines autorisées de l'application Thé Tip Top.
 * À appeler avant verify_signed_request() sur les endpoints appelés
 * depuis le navigateur.
 */
function apply_cors(): void
{
    $allowedOrigins = [
        'https://thetiptop.onrender.com',
        'https://thetiptop-preprod.onrender.com',
        'https://thetiptop-dev.onrender.com',
    ];
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    if (in_array($origin, $allowedOrigins, true)) {
        header("Access-Control-Allow-Origin: $origin");
        header('Access-Control-Allow-Headers: X-Api-Key, X-Timestamp, X-Signature, Content-Type');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    }
    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
        http_response_code(204);
        exit;
    }
}
