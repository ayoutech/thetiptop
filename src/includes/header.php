<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/database.php';

$page_title = $page_title ?? 'Thé Tip Top — Jeux-Concours | Thés du Sahara Marocain';
$user = $_SESSION['user'] ?? null;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Thé Tip Top — Thés bio et handmade du Sahara marocain. Participez au jeu-concours 100% gagnant pour l'ouverture de notre 10e boutique à Nice.">
    <title><?= htmlspecialchars($page_title) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css">
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-XXXXXXXXXX"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', 'G-XXXXXXXXXX');
    </script>
</head>
<body>

<!-- Bandeau RGPD -->
<div id="cookie-banner" class="cookie-banner" style="display:none;">
    <div class="container d-flex justify-content-between align-items-center py-2">
        <p class="mb-0 small">Nous utilisons des cookies pour améliorer votre expérience. <a href="/pages/mentions-legales.php" class="text-warning">En savoir plus</a></p>
        <button onclick="acceptCookies()" class="btn btn-sm ms-3" style="background:var(--or);color:#1a3a1a;font-weight:700;">Accepter</button>
    </div>
</div>

<nav class="navbar navbar-expand-lg navbar-dark" style="background:linear-gradient(135deg,#1a3a1a,#2C5F2E);border-bottom:2px solid #C9942A;">
    <div class="container">
        <!-- LOGO -->
        <a class="navbar-brand d-flex align-items-center gap-2" href="/">
            <svg width="36" height="36" viewBox="0 0 36 36" xmlns="http://www.w3.org/2000/svg">
                <circle cx="18" cy="18" r="17" fill="#1a3a1a" stroke="#C9942A" stroke-width="2"/>
                <text x="18" y="14" text-anchor="middle" fill="#C9942A" font-size="10" font-family="serif">☽</text>
                <path d="M10 20 Q18 14 26 20 Q18 26 10 20Z" fill="#C9942A" opacity="0.8"/>
                <circle cx="18" cy="20" r="3" fill="#4A7C59"/>
            </svg>
            <span style="font-family:'Playfair Display',serif;font-size:1.2rem;letter-spacing:1px;">
                <span style="color:#C9942A;">THÉ</span>
                <span style="color:white;"> TIP TOP</span>
            </span>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav ms-auto align-items-center gap-2">
                <li class="nav-item"><a class="nav-link" href="/" style="font-family:'Lato',sans-serif;letter-spacing:1px;">Accueil</a></li>
                <li class="nav-item"><a class="nav-link" href="/pages/participation.php" style="font-family:'Lato',sans-serif;letter-spacing:1px;">Participer</a></li>
                <?php if ($user): ?>
                    <li class="nav-item"><a class="nav-link" href="/pages/mon-compte.php">Mon compte</a></li>
                    <?php if ($user['role'] === 'admin'): ?>
                        <li class="nav-item"><a class="nav-link" style="color:#E8B84B;" href="/pages/admin.php">Admin</a></li>
                    <?php endif; ?>
                    <?php if ($user['role'] === 'employe'): ?>
                        <li class="nav-item"><a class="nav-link" style="color:#E8B84B;" href="/pages/employe.php">Espace boutique</a></li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="btn btn-sm btn-outline-light" href="/pages/deconnexion.php" style="border-radius:50px;">Déconnexion</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item"><a class="btn btn-sm btn-outline-light" href="/pages/connexion.php" style="border-radius:50px;">Connexion</a></li>
                    <li class="nav-item">
                        <a class="btn btn-sm" href="/pages/inscription.php" style="background:linear-gradient(135deg,#C9942A,#E8B84B);color:#1a3a1a;font-weight:700;border-radius:50px;">S'inscrire</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
