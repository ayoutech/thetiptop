<?php
use PHPUnit\Framework\TestCase;
use Ttt\Tests\AppServer;
use Ttt\Tests\Db;
use Ttt\Tests\Http;

/**
 * TESTS END-TO-END (HTTP) — parcours complets d'un visiteur sur l'application réelle :
 * inscription -> participation avec un code -> déconnexion -> reconnexion, contrôle d'accès par rôle.
 * Chaque test utilise son propre navigateur simulé (cookies isolés).
 */
final class UserJourneyTest extends TestCase
{
    private static string $base;
    private const ADMIN_PASSWORD = 'password';    // mot de passe des comptes de démonstration (cf. document d'accès)

    public static function setUpBeforeClass(): void
    {
        Db::reset();
        self::$base = AppServer::start();
    }

    protected function setUp(): void
    {
        Db::pdo()->exec('DELETE FROM tirage_final');
        Db::pdo()->exec('DELETE FROM tickets');
        Db::pdo()->exec("DELETE FROM users WHERE role = 'client'");
    }

    private function register(Http $browser, array $override = []): array
    {
        return $browser->postForm(self::$base . '/pages/inscription.php', array_merge([
            'prenom' => 'Camille', 'nom' => 'Durand', 'email' => 'camille@example.test', 'age' => 29,
            'sexe' => 'femme', 'mot_de_passe' => 'MotDePasse123', 'mot_de_passe2' => 'MotDePasse123',
        ], $override));
    }

    private function login(Http $browser, string $email, string $password): array
    {
        return $browser->postForm(self::$base . '/pages/connexion.php', ['email' => $email, 'mot_de_passe' => $password]);
    }

    // ---------- pages publiques ----------

    public static function pagesPubliques(): array
    {
        return [['/'], ['/pages/connexion.php'], ['/pages/inscription.php'], ['/pages/reglement.php'],
                ['/pages/mentions-legales.php'], ['/pages/confidentialite.php']];
    }

    /** @dataProvider pagesPubliques */
    public function testPagePubliqueAccessible(string $path): void
    {
        $r = (new Http())->get(self::$base . $path);
        $this->assertSame(200, $r['status'], $path);
        $this->assertStringContainsString('Thé Tip Top', $r['body']);
    }

    // ---------- parcours principal ----------

    public function testParcoursInscriptionParticipationEtGain(): void
    {
        Db::insertTicket('GAGNANT001', 'infuseur');
        Db::insertTicket('GAGNANT002', 'coffret_69');
        $b = new Http();

        $r = $this->register($b);
        $this->assertSame(302, $r['status']);
        $this->assertSame('/pages/participation.php', $r['headers']['location']);

        $page = $b->get(self::$base . '/pages/participation.php');
        $this->assertSame(200, $page['status']);

        $bad = $b->postForm(self::$base . '/pages/participation.php', ['code' => 'ZZZZZZZZZZ']);
        $this->assertStringContainsString('invalide', $bad['body']);

        $short = $b->postForm(self::$base . '/pages/participation.php', ['code' => 'ABC']);
        $this->assertStringContainsString('10 caractères', $short['body']);

        $ok = $b->postForm(self::$base . '/pages/participation.php', ['code' => 'gagnant001']);   // minuscules acceptées
        $this->assertStringContainsString('Infuseur à thé', $ok['body']);

        $again = $b->postForm(self::$base . '/pages/participation.php', ['code' => 'GAGNANT001']);
        $this->assertStringContainsString('déjà été utilisé', $again['body']);

        $second = $b->postForm(self::$base . '/pages/participation.php', ['code' => 'GAGNANT002']);
        $this->assertStringContainsString('Coffret découverte 69', $second['body']);

        $uid = (int)Db::scalar('SELECT id FROM users WHERE email = ?', ['camille@example.test']);
        $this->assertSame(2, (int)Db::scalar('SELECT COUNT(*) FROM tickets WHERE user_id = ? AND utilise = 1', [$uid]));
        $this->assertSame(1, (int)Db::scalar('SELECT COUNT(*) FROM tirage_final WHERE user_id = ?', [$uid]),
            'Un seul bulletin dans le tirage final, quel que soit le nombre de codes');
    }

    public function testUnCodeNePeutServirQuUneFoisPourDeuxUtilisateurs(): void
    {
        Db::insertTicket('UNIQUE0001', 'the_detox');
        $a = new Http();
        $b = new Http();
        $this->register($a, ['email' => 'a@example.test']);
        $this->register($b, ['email' => 'b@example.test']);

        $ra = $a->postForm(self::$base . '/pages/participation.php', ['code' => 'UNIQUE0001']);
        $rb = $b->postForm(self::$base . '/pages/participation.php', ['code' => 'UNIQUE0001']);

        $this->assertStringContainsString('Thé détox', $ra['body']);
        $this->assertStringContainsString('déjà été utilisé', $rb['body']);
    }

    /**
     * Régression : huit participants saisissent EN MÊME TEMPS le même code ; un seul doit gagner.
     * (Avant correction, plusieurs participants recevaient le même lot.)
     */
    public function testUnCodeNeGagneQuUneFoisSousForteConcurrence(): void
    {
        $players = 8;
        for ($round = 0; $round < 4; $round++) {
            $code = sprintf('RACE%06d', $round);
            Db::insertTicket($code, 'infuseur');
            $browsers = [];
            for ($i = 0; $i < $players; $i++) {
                $b = new Http();
                $this->register($b, ['email' => "r{$round}u{$i}@example.test"]);
                $browsers[] = $b;
            }
            $multi = curl_multi_init();
            $handles = [];
            foreach ($browsers as $b) {
                $token = $b->csrfToken(self::$base . '/pages/participation.php');
                $ch = curl_init(self::$base . '/pages/participation.php');
                curl_setopt_array($ch, [CURLOPT_POST => true, CURLOPT_POSTFIELDS => 'code=' . $code . '&csrf_token=' . $token,
                    CURLOPT_RETURNTRANSFER => true, CURLOPT_COOKIEFILE => $b->jarPath()]);
                curl_multi_add_handle($multi, $ch);
                $handles[] = $ch;
            }
            do {
                curl_multi_exec($multi, $running);
                curl_multi_select($multi);
            } while ($running);
            $winners = 0;
            foreach ($handles as $ch) {
                if (str_contains((string)curl_multi_getcontent($ch), 'Infuseur à thé')) {
                    $winners++;
                }
            }
            $this->assertSame(1, $winners, "Tour $round : le code $code ne doit avoir qu'un seul gagnant");
            $this->assertSame(1, (int)Db::scalar('SELECT COUNT(*) FROM tickets WHERE code = ? AND utilise = 1', [$code]));
        }
    }

    public function testDeconnexionPuisReconnexion(): void
    {
        $b = new Http();
        $this->register($b);

        $out = $b->get(self::$base . '/pages/deconnexion.php');
        $this->assertSame(302, $out['status']);

        $guard = $b->get(self::$base . '/pages/participation.php');
        $this->assertSame(302, $guard['status']);
        $this->assertStringContainsString('/pages/connexion.php', $guard['headers']['location']);

        $bad = $this->login($b, 'camille@example.test', 'mauvais');
        $this->assertStringContainsString('incorrect', $bad['body']);

        $good = $this->login($b, 'camille@example.test', 'MotDePasse123');
        $this->assertSame(302, $good['status']);
        $this->assertSame('/pages/mon-compte.php', $good['headers']['location']);
        $this->assertSame(200, $b->get(self::$base . '/pages/mon-compte.php')['status']);
    }

    // ---------- validation du formulaire d'inscription ----------

    public static function inscriptionsInvalides(): array
    {
        return [
            'mineur'                 => [['age' => 17], 'au moins 18 ans'],
            'mot de passe trop court' => [['mot_de_passe' => 'court', 'mot_de_passe2' => 'court'], 'au moins 8 caractères'],
            'mots de passe différents' => [['mot_de_passe2' => 'Different123'], 'ne correspondent pas'],
            'email invalide'         => [['email' => 'pas-un-email'], 'email invalide'],
            'champ manquant'         => [['nom' => ''], 'champs obligatoires'],
        ];
    }

    /** @dataProvider inscriptionsInvalides */
    public function testInscriptionRefusee(array $override, string $message): void
    {
        $r = $this->register(new Http(), $override);
        $this->assertSame(200, $r['status'], 'Pas de redirection : le formulaire est réaffiché');
        $this->assertStringContainsString($message, $r['body']);
        $this->assertSame(0, (int)Db::scalar("SELECT COUNT(*) FROM users WHERE role = 'client'"));
    }

    public function testEmailDejaUtiliseRefuse(): void
    {
        $this->register(new Http());
        $r = $this->register(new Http());
        $this->assertStringContainsString('déjà utilisée', $r['body']);
        $this->assertSame(1, (int)Db::scalar("SELECT COUNT(*) FROM users WHERE email = 'camille@example.test'"));
    }

    public function testMotDePasseStockeHache(): void
    {
        $this->register(new Http());
        $hash = Db::scalar('SELECT password FROM users WHERE email = ?', ['camille@example.test']);
        $this->assertNotSame('MotDePasse123', $hash);
        $this->assertTrue(password_verify('MotDePasse123', $hash));
    }

    // ---------- contrôle d'accès par rôle ----------

    public function testPagesProtegeesRedirigentLesAnonymes(): void
    {
        $b = new Http();
        foreach (['/pages/admin.php', '/pages/employe.php'] as $path) {
            $r = $b->get(self::$base . $path);
            $this->assertSame(302, $r['status'], $path);
            $this->assertSame('/', $r['headers']['location'], $path);
        }
        $this->assertSame(302, $b->get(self::$base . '/pages/mon-compte.php')['status']);
    }

    public function testUnClientNAccedePasAuxEspacesAdminEtEmploye(): void
    {
        $b = new Http();
        $this->register($b);
        foreach (['/pages/admin.php', '/pages/employe.php'] as $path) {
            $r = $b->get(self::$base . $path);
            $this->assertSame(302, $r['status'], $path);
            $this->assertSame('/', $r['headers']['location'], $path);
        }
    }

    public function testAdminAccedeALEspaceAdministration(): void
    {
        $b = new Http();
        $r = $this->login($b, 'admin@thetiptop.fr', self::ADMIN_PASSWORD);
        $this->assertSame('/pages/admin.php', $r['headers']['location']);
        $this->assertSame(200, $b->get(self::$base . '/pages/admin.php')['status']);
    }

    public function testEmployeAccedeALEspaceBoutiqueMaisPasAAdmin(): void
    {
        $b = new Http();
        $r = $this->login($b, 'employe@thetiptop.fr', self::ADMIN_PASSWORD);
        $this->assertSame('/pages/employe.php', $r['headers']['location']);
        $this->assertSame(200, $b->get(self::$base . '/pages/employe.php')['status']);
        $this->assertSame(302, $b->get(self::$base . '/pages/admin.php')['status']);
    }

    public function testAdminEtEmployeNeParticipentPasAuJeu(): void
    {
        $b = new Http();
        $this->login($b, 'admin@thetiptop.fr', self::ADMIN_PASSWORD);
        $r = $b->get(self::$base . '/pages/participation.php');
        $this->assertSame(302, $r['status']);
        $this->assertSame('/pages/admin.php', $r['headers']['location']);
    }

    // ---------- attaques courantes ----------

    public function testInjectionSqlSurLaConnexion(): void
    {
        $r = $this->login(new Http(), "admin@thetiptop.fr' OR '1'='1", "x' OR '1'='1");
        $this->assertSame(200, $r['status']);
        $this->assertStringContainsString('incorrect', $r['body']);
    }

    public function testXssStockeeEstEchappeeSurLaPageCompte(): void
    {
        $b = new Http();
        $this->register($b, ['prenom' => '<script>alert(1)</script>', 'nom' => '<img src=x onerror=alert(2)>']);
        $page = $b->get(self::$base . '/pages/mon-compte.php');
        $this->assertSame(200, $page['status']);
        $this->assertStringNotContainsString('<script>alert(1)</script>', $page['body']);
        $this->assertStringNotContainsString('<img src=x onerror=alert(2)>', $page['body']);
    }
}
