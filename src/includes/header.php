<?php
session_start();
require_once __DIR__ . '/../config/database.php';

$page_title = $page_title ?? 'Thé Tip Top — Jeu-Concours';
$user = $_SESSION['user'] ?? null;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Participez au jeu-concours Thé Tip Top — 100% gagnant ! Entrez votre code et découvrez votre lot.">
    <title><?= htmlspecialchars($page_title) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/assets/css/style.css">
    <!-- Google Analytics GA4 -->
    <!-- Remplacer G-XXXXXXXXXX par votre ID GA4 -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-XXXXXXXXXX"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', 'G-XXXXXXXXXX');
    </script>
</head>
<body>

<!-- Bandeau RGPD cookies -->
<div id="cookie-banner" class="cookie-banner" style="display:none;">
    <div class="container d-flex justify-content-between align-items-center py-2">
        <p class="mb-0 small">Nous utilisons des cookies pour améliorer votre expérience. <a href="/pages/mentions-legales.php">En savoir plus</a></p>
        <button onclick="acceptCookies()" class="btn btn-sm btn-success ms-3">Accepter</button>
    </div>
</div>

<nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #1B4332;">
    <div class="container">
        <a class="navbar-brand fw-bold" href="/">
            <span style="color: #B5860D;">THÉ</span> TIP TOP
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav ms-auto align-items-center gap-2">
                <li class="nav-item"><a class="nav-link" href="/">Accueil</a></li>
                <li class="nav-item"><a class="nav-link" href="/pages/participation.php">Participer</a></li>
                <?php if ($user): ?>
                    <li class="nav-item"><a class="nav-link" href="/pages/mon-compte.php">Mon compte</a></li>
                    <?php if ($user['role'] === 'admin'): ?>
                        <li class="nav-item"><a class="nav-link text-warning" href="/pages/admin.php">Admin</a></li>
                    <?php endif; ?>
                    <?php if ($user['role'] === 'employe'): ?>
                        <li class="nav-item"><a class="nav-link text-warning" href="/pages/employe.php">Espace boutique</a></li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="btn btn-outline-light btn-sm" href="/pages/deconnexion.php">Déconnexion</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item"><a class="btn btn-outline-light btn-sm" href="/pages/connexion.php">Connexion</a></li>
                    <li class="nav-item"><a class="btn btn-sm text-white" style="background-color:#B5860D;" href="/pages/inscription.php">S'inscrire</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
