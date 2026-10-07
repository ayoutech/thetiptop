<?php
use PHPUnit\Framework\TestCase;
use Ttt\Tests\AppServer;
use Ttt\Tests\Db;
use Ttt\Tests\Http;

/**
 * TESTS END-TO-END — protection CSRF et suppression de compte (droit à l'effacement RGPD).
 */
final class CsrfAndAccountTest extends TestCase
{
    private static string $base;

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

    private function registeredBrowser(string $email = 'csrf@example.test'): Http
    {
        $b = new Http();
        $r = $b->postForm(self::$base . '/pages/inscription.php', [
            'prenom' => 'Test', 'nom' => 'Csrf', 'email' => $email, 'age' => 30, 'sexe' => 'Femme',
            'mot_de_passe' => 'MotDePasse123', 'mot_de_passe2' => 'MotDePasse123']);
        $this->assertSame(302, $r['status']);
        return $b;
    }

    public static function formulairesPublics(): array
    {
        return [['/pages/connexion.php'], ['/pages/inscription.php'], ['/pages/newsletter.php']];
    }

    /** @dataProvider formulairesPublics */
    public function testLeFormulaireContientUnJetonCsrf(string $path): void
    {
        $page = (new Http())->get(self::$base . $path);
        $this->assertMatchesRegularExpression('/name="csrf_token" value="[a-f0-9]{64}"/', $page['body'], $path);
    }

    public function testLeFormulaireDeParticipationContientUnJeton(): void
    {
        $b = $this->registeredBrowser();
        $this->assertMatchesRegularExpression('/name="csrf_token" value="[a-f0-9]{64}"/',
            $b->get(self::$base . '/pages/participation.php')['body']);
    }

    public static function postsSansJeton(): array
    {
        return [
            'connexion'    => ['/pages/connexion.php', ['email' => 'a@b.test', 'mot_de_passe' => 'x']],
            'inscription'  => ['/pages/inscription.php', ['prenom' => 'A', 'nom' => 'B', 'email' => 'c@d.test', 'age' => 30,
                                                          'sexe' => 'Femme', 'mot_de_passe' => 'MotDePasse123', 'mot_de_passe2' => 'MotDePasse123']],
            'newsletter'   => ['/pages/newsletter.php', ['newsletter_email' => 'n@example.test']],
        ];
    }

    /** @dataProvider postsSansJeton */
    public function testPostSansJetonRefuse(string $path, array $fields): void
    {
        $r = (new Http())->post(self::$base . $path, $fields);
        $this->assertSame(403, $r['status'], $path);
        $this->assertSame(0, (int)Db::scalar("SELECT COUNT(*) FROM users WHERE role = 'client'"));
        $this->assertSame(0, (int)Db::scalar("SELECT COUNT(*) FROM newsletter_subscribers WHERE email = 'n@example.test'"));
    }

    public function testPostAvecMauvaisJetonRefuse(): void
    {
        $b = new Http();
        $b->get(self::$base . '/pages/connexion.php');
        $r = $b->post(self::$base . '/pages/connexion.php',
            ['email' => 'admin@thetiptop.fr', 'mot_de_passe' => 'password', 'csrf_token' => str_repeat('a', 64)]);
        $this->assertSame(403, $r['status']);
    }

    public function testUnJetonNEstPasUtilisableDansUneAutreSession(): void
    {
        $victim = new Http();
        $token = $victim->csrfToken(self::$base . '/pages/connexion.php');
        $attacker = new Http();     // autre session : ne connaît pas le jeton de la victime
        $r = $attacker->post(self::$base . '/pages/connexion.php',
            ['email' => 'admin@thetiptop.fr', 'mot_de_passe' => 'password', 'csrf_token' => $token]);
        $this->assertSame(403, $r['status']);
    }

    public function testParticipationSansJetonRefuseeEtCodeNonConsomme(): void
    {
        Db::insertTicket('CSRF000001', 'infuseur');
        $b = $this->registeredBrowser();
        $r = $b->post(self::$base . '/pages/participation.php', ['code' => 'CSRF000001']);   // formulaire forgé depuis un autre site
        $this->assertSame(403, $r['status']);
        $this->assertSame(0, (int)Db::scalar('SELECT utilise FROM tickets WHERE code = ?', ['CSRF000001']));
    }

    // ---------- suppression de compte ----------

    public function testSuppressionDeCompteRefuseeEnGet(): void
    {
        $b = $this->registeredBrowser();
        $r = $b->get(self::$base . '/pages/supprimer-compte.php');       // simple lien piégé
        $this->assertSame(405, $r['status']);
        $this->assertSame(1, (int)Db::scalar("SELECT COUNT(*) FROM users WHERE email = 'csrf@example.test'"), 'Le compte ne doit pas être supprimé');
    }

    public function testSuppressionDeCompteRefuseeSansJeton(): void
    {
        $b = $this->registeredBrowser();
        $r = $b->post(self::$base . '/pages/supprimer-compte.php', []);
        $this->assertSame(403, $r['status']);
        $this->assertSame(1, (int)Db::scalar("SELECT COUNT(*) FROM users WHERE email = 'csrf@example.test'"));
    }

    public function testDroitALEffacementAvecJetonValide(): void
    {
        Db::insertTicket('RGPD000002', 'the_detox');
        $b = $this->registeredBrowser();
        $b->postForm(self::$base . '/pages/participation.php', ['code' => 'RGPD000002']);
        $uid = (int)Db::scalar("SELECT id FROM users WHERE email = 'csrf@example.test'");
        $this->assertSame(1, (int)Db::scalar('SELECT COUNT(*) FROM tirage_final WHERE user_id = ?', [$uid]));

        $r = $b->postForm(self::$base . '/pages/mon-compte.php', [], self::$base . '/pages/supprimer-compte.php');
        $this->assertSame(302, $r['status']);
        $this->assertSame(0, (int)Db::scalar('SELECT COUNT(*) FROM users WHERE id = ?', [$uid]), 'compte supprimé');
        $this->assertSame(0, (int)Db::scalar('SELECT COUNT(*) FROM tirage_final WHERE user_id = ?', [$uid]), 'bulletin supprimé');
        $this->assertNull(Db::scalar('SELECT user_id FROM tickets WHERE code = ?', ['RGPD000002']), 'ticket détaché du compte');
    }

    public function testActionsAdminEtEmployeProtegees(): void
    {
        $admin = new Http();
        $admin->postForm(self::$base . '/pages/connexion.php', ['email' => 'admin@thetiptop.fr', 'mot_de_passe' => 'password']);
        $r = $admin->post(self::$base . '/pages/tirage.php', ['lancer_tirage' => '1']);
        $this->assertSame(403, $r['status'], 'Le lancement du tirage exige un jeton');

        $emp = new Http();
        $emp->postForm(self::$base . '/pages/connexion.php', ['email' => 'employe@thetiptop.fr', 'mot_de_passe' => 'password']);
        $r = $emp->post(self::$base . '/pages/employe.php', ['marquer_remis' => '1', 'ticket_id' => 1]);
        $this->assertSame(403, $r['status'], 'La remise d\'un lot exige un jeton');
    }
}
