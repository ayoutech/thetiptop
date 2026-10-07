<?php
namespace Ttt\Tests;

/**
 * Lance l'application réelle (src/) avec le serveur PHP intégré, branchée sur la base de test.
 * Utilisé par les suites "api" et "e2e" : on teste le vrai code via de vraies requêtes HTTP.
 */
final class AppServer
{
    private static $proc = null;
    private static string $base = '';

    public static function start(): string
    {
        if (self::$proc !== null) {
            return self::$base;
        }
        $sock = stream_socket_server('tcp://127.0.0.1:0');
        $port = (int)explode(':', stream_socket_get_name($sock, false))[1];
        fclose($sock);

        $env = array_merge(getenv(), ['API_HMAC_SECRET' => TTT_TEST_SECRET, 'PHP_CLI_SERVER_WORKERS' => '8']);
        self::$proc = proc_open(
            [PHP_BINARY, '-S', "127.0.0.1:$port", '-t', TTT_SRC],
            [0 => ['pipe', 'r'], 1 => ['file', sys_get_temp_dir() . '/ttt_server.log', 'a'], 2 => ['file', sys_get_temp_dir() . '/ttt_server.log', 'a']],
            $pipes, TTT_SRC, $env
        );
        self::$base = "http://127.0.0.1:$port";
        register_shutdown_function([self::class, 'stop']);

        for ($i = 0; $i < 50; $i++) {
            $c = @fsockopen('127.0.0.1', $port, $e, $s, 0.2);
            if ($c) {
                fclose($c);
                return self::$base;
            }
            usleep(100000);
        }
        throw new \RuntimeException('Le serveur de test n\'a pas démarré');
    }

    public static function stop(): void
    {
        if (self::$proc !== null) {
            proc_terminate(self::$proc);
            self::$proc = null;
        }
    }
}
