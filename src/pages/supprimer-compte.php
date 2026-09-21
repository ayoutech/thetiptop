<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['user_id'])) {
    header('Location: /pages/connexion.php');
    exit;
}

require_once __DIR__ . '/../config/database.php';
$db = getDB();

$user_id = $_SESSION['user_id'];

// Supprimer les données liées (RGPD : droit à l'effacement)
$db->prepare("DELETE FROM tirage_final WHERE user_id = ?")->execute([$user_id]);
$db->prepare("UPDATE tickets SET user_id = NULL WHERE user_id = ?")->execute([$user_id]);
$db->prepare("DELETE FROM users WHERE id = ?")->execute([$user_id]);

session_destroy();
header('Location: /?compte_supprime=1');
exit;