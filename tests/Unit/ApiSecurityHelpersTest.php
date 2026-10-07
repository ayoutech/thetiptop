<?php
use PHPUnit\Framework\TestCase;

/**
 * TESTS UNITAIRES — fonctions pures de src/api/security.php (aucune base, aucun réseau).
 */
final class ApiSecurityHelpersTest extends TestCase
{
    public static function httpsCases(): array
    {
        return [
            'HTTPS direct'                 => [['HTTPS' => 'on'], true],
            'derrière le proxy Render'     => [['HTTP_X_FORWARDED_PROTO' => 'https'], true],
            'HTTPS=off (Apache/IIS)'       => [['HTTPS' => 'off'], false],
            'HTTP simple'                  => [['HTTP_X_FORWARDED_PROTO' => 'http'], false],
            'aucun indice'                 => [[], false],
            'valeur vide'                  => [['HTTPS' => ''], false],
        ];
    }

    /** @dataProvider httpsCases */
    public function testDetectionHttps(array $server, bool $expected): void
    {
        $this->assertSame($expected, api_is_https($server));
    }

    public static function timestampCases(): array
    {
        $now = 1_800_000_000;
        return [
            'maintenant'              => [(string)$now, true],
            'il y a 299 s'            => [(string)($now - 299), true],
            'limite exacte (300 s)'   => [(string)($now - 300), true],
            'il y a 301 s (rejeu)'    => [(string)($now - 301), false],
            'dans 301 s (futur)'      => [(string)($now + 301), false],
            'texte'                   => ['abc', false],
            'négatif'                 => ['-5', false],
            'décimal'                 => ['12.5', false],
            'vide'                    => ['', false],
            'injection'               => ['1800000000; DROP TABLE', false],
        ];
    }

    /** @dataProvider timestampCases */
    public function testFenetreAntiRejeu(string $ts, bool $expected): void
    {
        $this->assertSame($expected, api_timestamp_is_valid($ts, 1_800_000_000));
    }

    public function testFormatDuPayloadSigne(): void
    {
        $this->assertSame('GET|/api/verify-code.php?code=ABC|1700000000|',
            api_build_payload('GET', '/api/verify-code.php?code=ABC', '1700000000', ''));
        $this->assertSame('POST|/x|1|{"a":1}', api_build_payload('POST', '/x', '1', '{"a":1}'));
    }

    /** Vecteur de test officiel RFC 4231 (cas n°2) : valide l'algorithme HMAC-SHA256 utilisé. */
    public function testVecteurOfficielRfc4231(): void
    {
        $this->assertSame('5bdcc146bf60754e6a042426089575c75a003f089d2739839dec58b964ec3843',
            api_sign('what do ya want for nothing?', 'Jefe'));
    }

    public function testToutChangementInvalideLaSignature(): void
    {
        $ref = api_sign(api_build_payload('GET', '/a?code=1', '100', ''), 'secret');
        $variants = [
            'autre secret'     => api_sign(api_build_payload('GET', '/a?code=1', '100', ''), 'autre'),
            'autre méthode'    => api_sign(api_build_payload('POST', '/a?code=1', '100', ''), 'secret'),
            'autre URL'        => api_sign(api_build_payload('GET', '/a?code=2', '100', ''), 'secret'),
            'autre horodatage' => api_sign(api_build_payload('GET', '/a?code=1', '101', ''), 'secret'),
            'autre corps'      => api_sign(api_build_payload('GET', '/a?code=1', '100', 'x'), 'secret'),
        ];
        foreach ($variants as $label => $sig) {
            $this->assertNotSame($ref, $sig, "La signature doit changer ($label)");
        }
        $this->assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $ref);
    }

    public function testParametresDeSecuriteDocumentes(): void
    {
        $this->assertSame(300, API_MAX_TIMESTAMP_DRIFT, 'Fenêtre anti-rejeu documentée : 5 minutes');
        $this->assertSame(60, API_RATE_LIMIT_PER_MINUTE, 'Rate limit documenté : 60 requêtes/minute');
    }
}
