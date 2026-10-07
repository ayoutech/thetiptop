<?php
/** Prépare la base pour le test navigateur : schéma neuf + codes de jeu connus. */
require __DIR__ . '/../bootstrap.php';
use Ttt\Tests\Db;
Db::reset();
foreach (['BROWSER001' => 'infuseur', 'BROWSER002' => 'coffret_69', 'BROWSER003' => 'the_detox'] as $code => $gain) {
    Db::insertTicket($code, $gain);
}
echo "Base de test prête (3 codes BROWSER00x)\n";
