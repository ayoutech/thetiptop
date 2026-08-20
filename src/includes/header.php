<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$page_title = $page_title ?? 'Thé Tip Top — Jeu-Concours 100% Gagnant';
$is_logged = isset($_SESSION['user_id']);

$user = null;
if ($is_logged) {
    require_once __DIR__ . '/../config/database.php';
    $db = getDB();
    $stmt = $db->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        session_destroy();
        $is_logged = false;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Thé Tip Top — Thés bio et handmade du Sahara marocain. Participez au jeu-concours 100% gagnant pour l'ouverture de notre 10e boutique à Nice.">
    <title><?= htmlspecialchars($page_title) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css">
    <!-- Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-WW2TK99XK2"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', 'G-WW2TK99XK2');
    </script>
</head>
<body>

<!-- NAV -->
<nav class="ttt-nav" id="mainNav">
    <a href="/" class="ttt-logo">
        <span class="ttt-logo-sym">☽</span>
        Thé Tip Top
    </a>

    <ul class="ttt-nav-links">
        <li><a href="/">Accueil</a></li>
        <?php if (!$is_logged || ($user && $user['role'] === 'client')): ?>
            <li><a href="/pages/participation.php">Participer</a></li>
        <?php endif; ?>
        <?php if ($is_logged): ?>
            <?php if ($user && $user['role'] === 'admin'): ?>
                <li><a href="/pages/admin.php">Administration</a></li>
            <?php elseif ($user && $user['role'] === 'employe'): ?>
                <li><a href="/pages/employe.php">Espace boutique</a></li>
            <?php else: ?>
                <li><a href="/pages/mon-compte.php">Mon compte</a></li>
            <?php endif; ?>
            <li><a href="/pages/deconnexion.php">Déconnexion</a></li>
        <?php else: ?>
            <li><a href="/pages/connexion.php">Connexion</a></li>
            <li><a href="/pages/inscription.php">S'inscrire</a></li>
        <?php endif; ?>
        <?php if (!$is_logged || ($user && $user['role'] === 'client')): ?>
            <li><a href="/pages/participation.php" class="btn-nav-cta">Entrer mon code</a></li>
        <?php endif; ?>
    </ul>

    <button class="ttt-hamburger" id="hamburger" aria-label="Menu">
        <span></span>
        <span></span>
        <span></span>
    </button>
</nav>

<!-- MENU MOBILE -->
<div class="ttt-mobile-menu" id="mobileMenu">
    <a href="/">Accueil</a>
    <?php if (!$is_logged || ($user && $user['role'] === 'client')): ?>
        <a href="/pages/participation.php">Participer</a>
    <?php endif; ?>
    <?php if ($is_logged): ?>
        <?php if ($user && $user['role'] === 'admin'): ?>
            <a href="/pages/admin.php">Administration</a>
        <?php elseif ($user && $user['role'] === 'employe'): ?>
            <a href="/pages/employe.php">Espace boutique</a>
        <?php else: ?>
            <a href="/pages/mon-compte.php">Mon compte</a>
        <?php endif; ?>
        <a href="/pages/deconnexion.php">Déconnexion</a>
    <?php else: ?>
        <a href="/pages/connexion.php">Connexion</a>
        <a href="/pages/inscription.php">S'inscrire</a>
    <?php endif; ?>
    <?php if (!$is_logged || ($user && $user['role'] === 'client')): ?>
        <a href="/pages/participation.php" style="color: var(--or) !important; font-weight: 700;">→ Entrer mon code</a>
    <?php endif; ?>
</div>