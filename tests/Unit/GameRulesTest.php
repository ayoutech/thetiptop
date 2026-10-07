<?php
use PHPUnit\Framework\TestCase;

/**
 * TESTS UNITAIRES — règles du jeu-concours décrites dans le cahier des charges,
 * vérifiées directement dans le code qui génère les codes (sans l'exécuter).
 */
final class GameRulesTest extends TestCase
{
    private function gains(): array
    {
        $src = file_get_contents(TTT_SRC . '/config/generate_codes.php');
        $this->assertSame(1, preg_match('/\$gains\s*=\s*\[(.*?)\];/s', $src, $m), 'Tableau $gains introuvable');
        preg_match_all("/'(\w+)'\s*=>\s*([\d_]+)/", $m[1], $mm, PREG_SET_ORDER);
        $out = [];
        foreach ($mm as $row) {
            $out[$row[1]] = (int)str_replace('_', '', $row[2]);
        }
        return $out;
    }

    public function testCinqLotsDefinis(): void
    {
        $this->assertSame(['infuseur', 'the_detox', 'the_signature', 'coffret_39', 'coffret_69'],
            array_keys($this->gains()));
    }

    public function testRepartitionDesLotsSurCinqCentMilleCodes(): void
    {
        $g = $this->gains();
        $this->assertSame(500000, array_sum($g), 'Le jeu annonce 500 000 codes');
        $this->assertSame(0.60, $g['infuseur'] / 500000);
        $this->assertSame(0.20, $g['the_detox'] / 500000);
        $this->assertSame(0.10, $g['the_signature'] / 500000);
        $this->assertSame(0.06, $g['coffret_39'] / 500000);
        $this->assertSame(0.04, $g['coffret_69'] / 500000);
    }

    public function testChaqueCodeADixCaracteresAlphanumeriques(): void
    {
        $src = file_get_contents(TTT_SRC . '/config/generate_codes.php');
        $this->assertStringContainsString("'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'", $src);
        $this->assertMatchesRegularExpression('/\$i\s*<\s*10/', $src, 'Les codes doivent faire 10 caractères');
    }
}
