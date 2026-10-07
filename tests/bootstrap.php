<?php
/**
 * Bootstrap des tests Thé Tip Top.
 * Les paramètres de base de données viennent de l'environnement (injectés par
 * ci/run-suite.sh dans Jenkins, ou par le développeur en local).
 */
define('TTT_ROOT', dirname(__DIR__));
define('TTT_SRC', TTT_ROOT . '/src');

// Secret HMAC propre à l'exécution : jamais de valeur réelle dans les tests.
$secret = getenv('API_HMAC_SECRET') ?: 'ci-secret-' . bin2hex(random_bytes(16));
putenv('API_HMAC_SECRET=' . $secret);
define('TTT_TEST_SECRET', $secret);

// Valeurs par défaut de la base de test (écrasées par l'environnement).
foreach (['DB_HOST' => '127.0.0.1', 'DB_PORT' => '3306', 'DB_NAME' => 'thetiptop_ci',
          'DB_USER' => 'root', 'DB_PASS' => ''] as $k => $v) {
    if (getenv($k) === false) {
        putenv("$k=$v");
    }
}

foreach (glob(__DIR__ . '/Support/*.php') as $file) {
    require_once $file;
}

// Fonctions de l'API (helpers purs + accès base) : chargées une fois pour toutes les suites.
require_once TTT_SRC . '/api/security.php';
