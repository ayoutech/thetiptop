<?php
use PHPUnit\Framework\TestCase;

/**
 * TESTS DE SÉCURITÉ (analyse statique du code, type SAST) :
 * secrets en clair, fonctions dangereuses, SQL non préparé, durcissement de la configuration.
 */
final class SourceCodeSecurityTest extends TestCase
{
    /** @return string[] fichiers PHP de l'application */
    private function phpFiles(): array
    {
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(TTT_SRC, FilesystemIterator::SKIP_DOTS));
        $files = [];
        foreach ($it as $f) {
            if ($f->isFile() && $f->getExtension() === 'php') {
                $files[] = $f->getPathname();
            }
        }
        sort($files);
        return $files;
    }

    /** @return string[] fichiers pouvant contenir des secrets (code, conf, pipeline) */
    private function configurableFiles(): array
    {
        $extra = array_filter([TTT_ROOT . '/Jenkinsfile', TTT_ROOT . '/Dockerfile', TTT_ROOT . '/apache.conf',
            TTT_ROOT . '/docker-compose.yml', TTT_ROOT . '/phpunit.xml'], 'is_file');
        return array_merge($this->phpFiles(), $extra, glob(TTT_ROOT . '/ci/*') ?: [], glob(TTT_ROOT . '/monitoring/*') ?: []);
    }

    public function testAucunSecretEnClairDansLeCode(): void
    {
        $patterns = [
            'clé privée'            => '/-----BEGIN (RSA |EC |OPENSSH |DSA )?PRIVATE KEY-----/',
            'clé AWS'               => '/AKIA[0-9A-Z]{16}/',
            'jeton GitHub'          => '/gh[pousr]_[A-Za-z0-9]{36,}|github_pat_[A-Za-z0-9_]{20,}/',
            'jeton Slack'           => '/xox[baprs]-[A-Za-z0-9-]{10,}/',
            'hook de déploiement Render' => '#api\.render\.com/deploy/srv-[a-z0-9]+\?key=[A-Za-z0-9_-]+#',
            'secret affecté en dur' => '/(secret|api[_-]?key|token|passwd|password)[\'"]?\s*[:=]>?\s*[\'"][A-Za-z0-9+\/=_\-]{24,}[\'"]/i',
        ];
        $hits = [];
        foreach ($this->configurableFiles() as $file) {
            $content = file_get_contents($file);
            foreach ($patterns as $label => $re) {
                if (preg_match($re, $content, $m)) {
                    $hits[] = basename($file) . " : $label";
                }
            }
        }
        $this->assertSame([], $hits, "Secrets détectés :\n" . implode("\n", $hits));
    }

    public function testLeSecretHmacVientDeLEnvironnement(): void
    {
        $src = file_get_contents(TTT_SRC . '/api/security.php');
        $this->assertMatchesRegularExpression("/getenv\('API_HMAC_SECRET'\)/", $src);
    }

    public function testAucuneFonctionDangereuse(): void
    {
        // (?<![>:\w$]) : on ignore les méthodes d'objet ($pdo->exec(), Classe::exec()), seuls les appels de fonction comptent
        $forbidden = '/(?<![>:\w$])(eval|exec|shell_exec|system|passthru|popen|proc_open|assert|create_function|phpinfo)\s*\(/';
        $hits = [];
        foreach ($this->phpFiles() as $file) {
            if (preg_match($forbidden, file_get_contents($file), $m)) {
                $hits[] = str_replace(TTT_SRC . '/', '', $file) . ' : ' . $m[1] . '()';
            }
        }
        $this->assertSame([], $hits);
    }

    public function testAucuneRequeteSqlConstruiteAvecLesDonneesUtilisateur(): void
    {
        $re = '/->(query|exec|prepare)\s*\([^;]*\$_(GET|POST|REQUEST|COOKIE|SERVER)\b/';
        $hits = [];
        foreach ($this->phpFiles() as $file) {
            if (preg_match($re, file_get_contents($file))) {
                $hits[] = str_replace(TTT_SRC . '/', '', $file);
            }
        }
        $this->assertSame([], $hits, 'Utiliser des requêtes préparées avec paramètres.');
    }

    public function testAucuneInterpolationDeVariableDansUneRequeteSql(): void
    {
        $re = '/->(query|exec)\s*\(\s*"[^"]*\$[a-zA-Z_]/';
        $hits = [];
        foreach ($this->phpFiles() as $file) {
            if (preg_match($re, file_get_contents($file))) {
                $hits[] = str_replace(TTT_SRC . '/', '', $file);
            }
        }
        $this->assertSame([], $hits);
    }

    public function testAucuneSortieNonEchappeeDesDonneesUtilisateur(): void
    {
        $re = '/(echo|print|<\?=)\s*\$_(GET|POST|REQUEST|COOKIE)\b/';
        $hits = [];
        foreach ($this->phpFiles() as $file) {
            if (preg_match($re, file_get_contents($file))) {
                $hits[] = str_replace(TTT_SRC . '/', '', $file);
            }
        }
        $this->assertSame([], $hits, 'Échapper avec htmlspecialchars()');
    }

    public function testMotsDePasseHachesAvecPasswordHash(): void
    {
        $inscription = file_get_contents(TTT_SRC . '/pages/inscription.php');
        $this->assertStringContainsString('password_hash(', $inscription);
        $this->assertStringContainsString('password_verify(', file_get_contents(TTT_SRC . '/pages/connexion.php'));
        foreach ($this->phpFiles() as $file) {
            $this->assertDoesNotMatchRegularExpression('/\b(md5|sha1)\s*\(\s*\$?[a-z_]*(mdp|pass)/i',
                file_get_contents($file), basename($file) . ' : hachage faible d\'un mot de passe');
        }
    }

    public function testLeDossierConfigNEstPasAccessibleDepuisLeWeb(): void
    {
        $this->assertStringContainsString('Deny from all', file_get_contents(TTT_SRC . '/config/.htaccess'));
    }

    // ---------- durcissement (CCTD §sécurité) ----------

    public function testEnTetesDeSecuriteHttpConfiguresDansApache(): void
    {
        $conf = file_get_contents(TTT_ROOT . '/apache.conf');
        foreach (['X-Content-Type-Options "nosniff"', 'X-Frame-Options', 'Referrer-Policy',
                  'Strict-Transport-Security', 'Permissions-Policy'] as $header) {
            $this->assertStringContainsString($header, $conf, "En-tête $header manquant");
        }
        $this->assertStringContainsString('a2enmod rewrite headers', file_get_contents(TTT_ROOT . '/Dockerfile'),
            'mod_headers doit être activé dans l\'image');
    }

    public function testCookieDeSessionDurci(): void
    {
        $ini = file_get_contents(TTT_ROOT . '/docker/security.ini');
        $this->assertMatchesRegularExpression('/session\.cookie_httponly\s*=\s*1/', $ini);
        $this->assertMatchesRegularExpression('/session\.cookie_samesite\s*=\s*Lax/', $ini);
        $this->assertMatchesRegularExpression('/session\.use_strict_mode\s*=\s*1/', $ini);
        $this->assertMatchesRegularExpression('/display_errors\s*=\s*Off/', $ini, 'Pas d\'erreurs PHP affichées aux visiteurs');
        $this->assertMatchesRegularExpression('/expose_php\s*=\s*Off/', $ini);
        $this->assertStringContainsString('zz-security.ini', file_get_contents(TTT_ROOT . '/Dockerfile'));
    }

    public function testIdentifiantDeSessionRegenereAuLoginEtALInscription(): void
    {
        foreach (['connexion', 'inscription'] as $page) {
            $this->assertStringContainsString('session_regenerate_id(true)',
                file_get_contents(TTT_SRC . "/pages/$page.php"), "$page.php : anti fixation de session");
        }
    }

    public function testChaqueFormulairePostPorteUnJetonCsrf(): void
    {
        $missing = [];
        foreach ($this->phpFiles() as $file) {
            $src = file_get_contents($file);
            $forms = preg_match_all('/<form\b[^>]*method\s*=\s*["\']post["\'][^>]*>(.*?)<\/form>/is', $src, $m);
            if ($forms) {
                foreach ($m[1] as $i => $body) {
                    if (!str_contains($body, 'csrf_field()')) {
                        $missing[] = str_replace(TTT_SRC . '/', '', $file) . ' (formulaire n°' . ($i + 1) . ')';
                    }
                }
            }
        }
        $this->assertSame([], $missing, 'Formulaires POST sans csrf_field() : ' . implode(', ', $missing));
    }

    public function testChaquePageQuiTraiteUnPostVerifieLeJeton(): void
    {
        $missing = [];
        foreach ($this->phpFiles() as $file) {
            $rel = str_replace(TTT_SRC . '/', '', $file);
            if (str_starts_with($rel, 'api/') || $rel === 'includes/csrf.php' || str_starts_with($rel, 'config/')) {
                continue; // l'API est protégée par la signature HMAC
            }
            $src = file_get_contents($file);
            $handlesPost = preg_match("/REQUEST_METHOD'\]\s*(===|!==)\s*'POST'/", $src);
            if ($handlesPost && !str_contains($src, 'csrf_verify()')) {
                $missing[] = $rel;
            }
        }
        $this->assertSame([], $missing, 'Pages traitant un POST sans csrf_verify() : ' . implode(', ', $missing));
    }

    public function testLeJetonCsrfEstAleatoireEtComparéEnTempsConstant(): void
    {
        $src = file_get_contents(TTT_SRC . '/includes/csrf.php');
        $this->assertStringContainsString('random_bytes(32)', $src);
        $this->assertStringContainsString('hash_equals(', $src);
    }

    public function testActionDestructiveInterditeEnGet(): void
    {
        $src = file_get_contents(TTT_SRC . '/pages/supprimer-compte.php');
        $this->assertMatchesRegularExpression("/REQUEST_METHOD'\]\s*\?\?\s*''\)\s*!==\s*'POST'/", $src);
    }
}
