<?php
namespace Ttt\Tests;

use PDO;

/** Accès à la base de test + remise à zéro du schéma depuis src/config/init.sql. */
final class Db
{
    private static ?PDO $pdo = null;

    public static function pdo(): PDO
    {
        if (self::$pdo === null) {
            $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                getenv('DB_HOST'), getenv('DB_PORT'), getenv('DB_NAME'));
            self::$pdo = new PDO($dsn, getenv('DB_USER'), getenv('DB_PASS') ?: '', [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => true, // permet d'exécuter init.sql en un seul exec()
            ]);
        }
        return self::$pdo;
    }

    /** Supprime toutes les tables puis rejoue init.sql : base identique à celle d'un nouvel environnement. */
    public static function reset(): void
    {
        $pdo = self::pdo();
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
        foreach ($pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN) as $table) {
            $pdo->exec('DROP TABLE IF EXISTS `' . $table . '`');
        }
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
        $pdo->exec(file_get_contents(TTT_SRC . '/config/init.sql'));
    }

    public static function insertUser(string $email, string $password, string $role = 'client',
                                      string $prenom = 'Test', string $nom = 'Utilisateur'): int
    {
        $st = self::pdo()->prepare(
            'INSERT INTO users (prenom, nom, email, age, sexe, password, role) VALUES (?,?,?,?,?,?,?)');
        $st->execute([$prenom, $nom, $email, 30, 'autre', password_hash($password, PASSWORD_DEFAULT), $role]);
        return (int)self::pdo()->lastInsertId();
    }

    public static function insertTicket(string $code, string $gain = 'infuseur', bool $utilise = false): void
    {
        self::pdo()->prepare('INSERT INTO tickets (code, gain, utilise) VALUES (?,?,?)')
            ->execute([$code, $gain, $utilise ? 1 : 0]);
    }

    public static function insertApiKey(string $key, bool $active = true): void
    {
        $pdo = self::pdo();
        $pdo->exec("CREATE TABLE IF NOT EXISTS api_keys (
            api_key VARCHAR(64) PRIMARY KEY, label VARCHAR(100) NOT NULL,
            environment VARCHAR(20) NOT NULL DEFAULT 'prod', active TINYINT(1) NOT NULL DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
        $pdo->prepare("INSERT INTO api_keys (api_key, label, environment, active) VALUES (?, 'test CI', 'ci', ?)")
            ->execute([$key, $active ? 1 : 0]);
    }

    public static function scalar(string $sql, array $params = [])
    {
        $st = self::pdo()->prepare($sql);
        $st->execute($params);
        return $st->fetchColumn();
    }
}
