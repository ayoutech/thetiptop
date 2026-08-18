<?php
require_once __DIR__ . '/database.php';

echo "Génération de 500 000 codes en cours...\n";

$gains = [
    'infuseur'      => 300000,
    'the_detox'     => 100000,
    'the_signature' => 50000,
    'coffret_39'    => 30000,
    'coffret_69'    => 20000,
];

$all_gains = [];
foreach ($gains as $gain => $qty) {
    for ($i = 0; $i < $qty; $i++) {
        $all_gains[] = $gain;
    }
}
shuffle($all_gains);

$db = getDB();
$db->beginTransaction();

$stmt = $db->prepare("INSERT IGNORE INTO tickets (code, gain) VALUES (?, ?)");
$batch = 0;
$total = 0;
$used_codes = [];

foreach ($all_gains as $gain) {
    do {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $code = '';
        for ($i = 0; $i < 10; $i++) {
            $code .= $chars[random_int(0, strlen($chars) - 1)];
        }
    } while (isset($used_codes[$code]));

    $used_codes[$code] = true;
    $stmt->execute([$code, $gain]);
    $total++;
    $batch++;

    if ($batch >= 10000) {
        $db->commit();
        $db->beginTransaction();
        $batch = 0;
        echo "Codes générés : $total / 500000\n";
        flush();
    }
}

$db->commit();
echo "✅ $total codes générés avec succès !\n";
