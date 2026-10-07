<?php
/**
 * Protection CSRF par jeton de synchronisation.
 *  - csrf_field()  : à placer dans chaque <form method="POST"> (champ caché).
 *  - csrf_verify() : à appeler en tête de chaque page qui traite un formulaire ; refuse (403) toute
 *                    requête POST dont le jeton est absent, faux ou périmé. Sans effet sur les requêtes GET.
 * Le jeton est lié à la session (aléatoire, 256 bits) et comparé en temps constant.
 * Les endpoints /api/* ne sont pas concernés : ils sont protégés par la signature HMAC.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES) . '">';
}

function csrf_verify(): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        return;
    }
    $sent = $_POST['csrf_token'] ?? '';
    $ok = is_string($sent) && !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $sent);
    if (!$ok) {
        http_response_code(403);
        exit('Requête refusée : jeton de sécurité invalide ou expiré. Rechargez la page et réessayez.');
    }
}
