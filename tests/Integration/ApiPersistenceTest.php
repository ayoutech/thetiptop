<?php
use PHPUnit\Framework\TestCase;
use Ttt\Tests\Clock;
use Ttt\Tests\Db;

/**
 * TESTS D'INTÉGRATION — fonctions d'accès base de src/api/security.php
 * (clés API, limitation de débit, journal des échecs) sur une vraie base.
 */
final class ApiPersistenceTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        Db::reset();
        Db::insertApiKey('key_active', true);
        Db::insertApiKey('key_revoquee', false);
        Clock::waitForSafeWindow(10);
    }

    public function testCleActiveTrouvee(): void
    {
        $row = lookup_api_key('key_active');
        $this->assertIsArray($row);
        $this->assertSame('key_active', $row['api_key']);
    }

    public function testCleRevoqueeOuInconnueRefusee(): void
    {
        $this->assertNull(lookup_api_key('key_revoquee'));
        $this->assertNull(lookup_api_key('inconnue'));
        $this->assertNull(lookup_api_key("' OR '1'='1"), 'Injection SQL sans effet');
    }

    public function testRateLimitBloqueAuDelaDeSoixanteRequetes(): void
    {
        for ($i = 1; $i <= API_RATE_LIMIT_PER_MINUTE; $i++) {
            $this->assertTrue(check_rate_limit('key_active'), "requête $i autorisée");
        }
        $this->assertFalse(check_rate_limit('key_active'), 'la 61e requête est refusée');
    }

    public function testRateLimitEstIndependantParCle(): void
    {
        $this->assertTrue(check_rate_limit('autre_cle'));
    }

    public function testEchecAuthentificationJournalise(): void
    {
        log_failed_auth('key_active', 'signature invalide');
        $row = Db::pdo()->query('SELECT api_key, reason FROM api_auth_failures ORDER BY id DESC LIMIT 1')->fetch();
        $this->assertSame('key_active', $row['api_key']);
        $this->assertSame('signature invalide', $row['reason']);
    }
}
