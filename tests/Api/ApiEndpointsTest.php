<?php
use PHPUnit\Framework\TestCase;
use Ttt\Tests\AppServer;
use Ttt\Tests\ApiClient;
use Ttt\Tests\Clock;
use Ttt\Tests\Db;
use Ttt\Tests\Http;

/**
 * TESTS D'API (contrat + sécurité) — vraies requêtes HTTP signées vers /api/*.php
 * (serveur PHP + base MariaDB de test). Couvre HTTPS, HMAC, anti-rejeu, clé API, rate limit, CORS.
 */
final class ApiEndpointsTest extends TestCase
{
    private static string $base;
    private static ApiClient $api;

    public static function setUpBeforeClass(): void
    {
        Db::reset();
        Db::insertApiKey('ttt_ci_key');
        Db::insertApiKey('ttt_ci_ratelimit');
        Db::insertTicket('ABCDE12345', 'infuseur', false);
        Db::insertTicket('USED000001', 'coffret_69', true);
        Db::insertTicket('FREE000002', 'the_signature', false);
        self::$base = AppServer::start();
        self::$api = new ApiClient(self::$base, 'ttt_ci_key');
        Clock::waitForSafeWindow(30);
    }

    // ---------- verify-code : cas nominaux ----------

    public function testCodeInconnu(): void
    {
        $r = self::$api->call('/api/verify-code.php?code=ZZZZZZZZZZ');
        $this->assertSame(404, $r['status']);
        $this->assertSame(['valid' => false], $r['json']);
    }

    public function testCodeValideNonUtiliseRenvoieLeGain(): void
    {
        $r = self::$api->call('/api/verify-code.php?code=ABCDE12345');
        $this->assertSame(200, $r['status']);
        $this->assertSame(['valid' => true, 'utilise' => false, 'gain' => 'infuseur'], $r['json']);
        $this->assertStringContainsString('application/json', $r['headers']['content-type']);
    }

    public function testCodeDejaUtiliseNeRevelePasLeGain(): void
    {
        $r = self::$api->call('/api/verify-code.php?code=USED000001');
        $this->assertSame(200, $r['status']);
        $this->assertTrue($r['json']['valid']);
        $this->assertTrue($r['json']['utilise']);
        $this->assertNull($r['json']['gain']);
    }

    public function testVerifierUnCodeNeLeConsommePas(): void
    {
        self::$api->call('/api/verify-code.php?code=FREE000002');
        $this->assertSame(0, (int)Db::scalar('SELECT utilise FROM tickets WHERE code = ?', ['FREE000002']));
    }

    public static function mauvaisFormats(): array
    {
        return [
            'trop court'       => ['ABC'],
            'minuscules'       => ['abcde12345'],
            '11 caractères'    => ['ABCDE123456'],
            'caractères spéc.' => ['ABCDE-2345'],
            'injection SQL'    => ["%27%20OR%20%271%27%3D%271"],
            'vide'             => [''],
        ];
    }

    /** @dataProvider mauvaisFormats */
    public function testFormatDeCodeInvalide(string $code): void
    {
        $r = self::$api->call('/api/verify-code.php?code=' . $code);
        $this->assertSame(400, $r['status']);
        $this->assertSame('Format de code invalide', $r['json']['error']);
    }

    // ---------- authentification ----------

    public function testHttpsObligatoire(): void
    {
        $r = self::$api->call('/api/verify-code.php?code=ABCDE12345', ['https' => false]);
        $this->assertSame(400, $r['status']);
        $this->assertSame('HTTPS requis', $r['json']['error']);
    }

    public static function enTetesManquants(): array
    {
        return [['X-Api-Key'], ['X-Timestamp'], ['X-Signature']];
    }

    /** @dataProvider enTetesManquants */
    public function testEnTeteManquant(string $header): void
    {
        $r = self::$api->call('/api/verify-code.php?code=ABCDE12345', ['drop' => [$header]]);
        $this->assertSame(401, $r['status']);
    }

    public function testRequeteRejoueeApresCinqMinutes(): void
    {
        $r = self::$api->call('/api/verify-code.php?code=ABCDE12345', ['timestamp' => time() - 301]);
        $this->assertSame(401, $r['status']);
        $this->assertSame('Requête expirée', $r['json']['error']);
    }

    public function testHorodatageDansLeFutur(): void
    {
        $r = self::$api->call('/api/verify-code.php?code=ABCDE12345', ['timestamp' => time() + 1000]);
        $this->assertSame(401, $r['status']);
    }

    public function testCleInconnue(): void
    {
        $r = self::$api->call('/api/verify-code.php?code=ABCDE12345', ['api_key' => 'cle_inconnue']);
        $this->assertSame(401, $r['status']);
        $this->assertSame('Clé API invalide', $r['json']['error']);
    }

    public function testMauvaiseSignature(): void
    {
        $r = self::$api->call('/api/verify-code.php?code=ABCDE12345', ['signature' => str_repeat('0', 64)]);
        $this->assertSame(401, $r['status']);
        $this->assertSame('Signature invalide', $r['json']['error']);
    }

    public function testMauvaisSecret(): void
    {
        $r = self::$api->call('/api/verify-code.php?code=ABCDE12345', ['secret' => 'pas-le-bon-secret']);
        $this->assertSame(401, $r['status']);
    }

    public function testSignatureNonReutilisableSurUneAutreRessource(): void
    {
        // signature calculée pour le code A, requête envoyée pour le code B
        $r = self::$api->call('/api/verify-code.php?code=USED000001',
            ['signed_uri' => '/api/verify-code.php?code=ABCDE12345']);
        $this->assertSame(401, $r['status']);
    }

    public function testEchecsJournalisesAvecLaRaison(): void
    {
        $reasons = Db::pdo()->query('SELECT reason FROM api_auth_failures')->fetchAll(PDO::FETCH_COLUMN);
        foreach (['signature invalide', 'clé API inconnue', 'timestamp expiré ou invalide'] as $expected) {
            $this->assertContains($expected, $reasons);
        }
    }

    // ---------- stats ----------

    public function testStatistiquesAgregees(): void
    {
        $r = self::$api->call('/api/stats.php');
        $this->assertSame(200, $r['status']);
        $this->assertSame(3, $r['json']['tickets_total']);
        $this->assertSame(1, $r['json']['tickets_utilises']);
        $this->assertEquals(33.33, $r['json']['taux_participation']);
    }

    public function testStatistiquesExigentUneSignature(): void
    {
        $r = (new Http())->get(self::$base . '/api/stats.php', ['X-Forwarded-Proto' => 'https']);
        $this->assertSame(401, $r['status']);
    }

    // ---------- CORS ----------

    public function testCorsAutoriseLesOriginesDeLApplication(): void
    {
        $r = self::$api->call('/api/stats.php', ['headers' => ['Origin' => 'https://thetiptop.onrender.com']]);
        $this->assertSame('https://thetiptop.onrender.com', $r['headers']['access-control-allow-origin'] ?? null);
    }

    public function testCorsRefuseUneOrigineEtrangere(): void
    {
        $r = self::$api->call('/api/stats.php', ['headers' => ['Origin' => 'https://evil.example']]);
        $this->assertArrayNotHasKey('access-control-allow-origin', $r['headers']);
    }

    public function testPreflightOptionsRepond204(): void
    {
        $r = (new Http())->request('OPTIONS', self::$base . '/api/stats.php',
            ['Origin' => 'https://thetiptop.onrender.com', 'X-Forwarded-Proto' => 'https']);
        $this->assertSame(204, $r['status']);
    }

    // ---------- rate limit (en dernier : consomme la fenêtre d'une clé dédiée) ----------

    public function testLimitationDeDebitA60RequetesParMinute(): void
    {
        $limited = new ApiClient(self::$base, 'ttt_ci_ratelimit');
        $statuses = [];
        for ($i = 1; $i <= 61; $i++) {
            $statuses[] = $limited->call('/api/verify-code.php?code=ZZZZZZZZZZ')['status'];
        }
        $this->assertSame(array_fill(0, 60, 404), array_slice($statuses, 0, 60), 'Les 60 premières passent');
        $this->assertSame(429, $statuses[60], 'La 61e est refusée');
    }
}
