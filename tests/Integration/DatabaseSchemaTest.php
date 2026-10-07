<?php
use PHPUnit\Framework\TestCase;
use Ttt\Tests\Db;

/**
 * TESTS D'INTÉGRATION — le code et le schéma réel (src/config/init.sql) sur une vraie base MariaDB.
 */
final class DatabaseSchemaTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        Db::reset();
    }

    public function testLesTroisTablesDuJeuExistent(): void
    {
        $tables = Db::pdo()->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
        foreach (['users', 'tickets', 'tirage_final'] as $t) {
            $this->assertContains($t, $tables);
        }
    }

    public function testComptesParDefautAdminEtEmploye(): void
    {
        $roles = Db::pdo()->query("SELECT email, role, password FROM users")->fetchAll(PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC);
        $this->assertSame('admin', $roles['admin@thetiptop.fr']['role']);
        $this->assertSame('employe', $roles['employe@thetiptop.fr']['role']);
        foreach ($roles as $email => $row) {
            $this->assertStringStartsWith('$2y$', $row['password'], "Mot de passe de $email non haché en bcrypt");
        }
    }

    public function testUnCodeEstUnique(): void
    {
        Db::insertTicket('UNIQUE0001');
        $this->expectException(PDOException::class);
        Db::insertTicket('UNIQUE0001');
    }

    public function testUnGainInconnuEstRefuse(): void
    {
        $this->expectException(PDOException::class);
        Db::pdo()->exec("INSERT INTO tickets (code, gain) VALUES ('BADGAIN001', 'voiture')");
    }

    public function testUnGainNulEstRefuse(): void
    {
        $this->expectException(PDOException::class);
        Db::pdo()->exec("INSERT INTO tickets (code, gain) VALUES ('NOGAIN0001', NULL)");
    }

    public function testClefEtrangereSurUtilisateur(): void
    {
        $this->expectException(PDOException::class);
        Db::pdo()->exec("INSERT INTO tickets (code, gain, user_id) VALUES ('FKTEST0001', 'infuseur', 999999)");
    }

    public function testSuppressionDeCompteConserveLeTicketEtEffaceLeTirage(): void
    {
        $uid = Db::insertUser('rgpd@example.test', 'MotDePasse123');
        Db::insertTicket('RGPD000001', 'the_detox', true);
        Db::pdo()->prepare('UPDATE tickets SET user_id = ? WHERE code = ?')->execute([$uid, 'RGPD000001']);
        Db::pdo()->prepare('INSERT INTO tirage_final (user_id) VALUES (?)')->execute([$uid]);

        Db::pdo()->prepare('DELETE FROM users WHERE id = ?')->execute([$uid]);

        $this->assertNull(Db::scalar('SELECT user_id FROM tickets WHERE code = ?', ['RGPD000001']), 'ON DELETE SET NULL');
        $this->assertSame(0, (int)Db::scalar('SELECT COUNT(*) FROM tirage_final WHERE user_id = ?', [$uid]), 'ON DELETE CASCADE');
    }

    public function testEmailUniquePourLesComptes(): void
    {
        Db::insertUser('doublon@example.test', 'MotDePasse123');
        $this->expectException(PDOException::class);
        Db::insertUser('doublon@example.test', 'AutreMotDePasse1');
    }
}
