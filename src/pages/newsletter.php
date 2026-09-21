<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

require_once __DIR__ . '/../config/database.php';

$success = false;
$error = '';

try {
    $db = getDB();
    $db->exec("CREATE TABLE IF NOT EXISTS newsletter_subscribers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) UNIQUE NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = trim($_POST['newsletter_email'] ?? '');
        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Adresse email invalide.';
        } else {
            $stmt = $db->prepare("INSERT IGNORE INTO newsletter_subscribers (email) VALUES (?)");
            $stmt->execute([$email]);
            $success = true;
        }
    }
} catch (Exception $e) {
    $error = 'Une erreur est survenue, réessayez plus tard.';
}

$page_title = 'Newsletter — Thé Tip Top';
require_once __DIR__ . '/../includes/header.php';
?>

<style>
.newsletter-page {
    min-height: calc(100vh - 68px);
    background: var(--blanc-casse);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 3rem 1.5rem;
}
.newsletter-card {
    background: #fff;
    border: 1px solid rgba(45,74,45,0.1);
    border-radius: 4px;
    padding: 3rem 2.5rem;
    max-width: 520px;
    width: 100%;
    text-align: center;
    box-shadow: 0 4px 32px rgba(26,46,26,0.06);
}
.newsletter-icon-big {
    width: 64px; height: 64px;
    background: var(--vert-nuit);
    color: var(--or);
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.8rem;
    margin: 0 auto 1.3rem;
}
.newsletter-page-title {
    font-family: 'Playfair Display', serif;
    font-size: 1.7rem;
    font-weight: 700;
    color: var(--vert-nuit);
    margin-bottom: 0.6rem;
}
.newsletter-page-sub {
    font-size: 0.9rem;
    color: #5a6e4a;
    line-height: 1.65;
    margin-bottom: 2rem;
}
.newsletter-input {
    width: 100%;
    padding: 13px 16px;
    border: 1px solid rgba(45,74,45,0.25);
    border-radius: 2px;
    font-size: 0.9rem;
    margin-bottom: 1rem;
    outline: none;
    transition: border-color 0.2s, box-shadow 0.2s;
}
.newsletter-input:focus { border-color: var(--or); box-shadow: 0 0 0 3px rgba(201,168,76,0.15); }
.newsletter-btn-page {
    width: 100%;
    background: var(--vert-nuit);
    color: var(--creme);
    padding: 13px;
    border: none;
    border-radius: 2px;
    font-size: 0.82rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    cursor: pointer;
    transition: background 0.2s;
}
.newsletter-btn-page:hover { background: var(--or); color: var(--vert-nuit); }
.alert-ttt { padding: 12px 16px; border-radius: 2px; font-size: 0.85rem; margin-bottom: 1.2rem; }
.alert-ok { background: #f0fdf4; border: 1px solid #c0e0c8; color: #1a4a2a; }
.alert-erreur { background: #fdf0f0; border: 1px solid #f0c0c0; color: #8b2020; }
</style>

<div class="newsletter-page">
    <div class="newsletter-card">
        <div class="newsletter-icon-big">✉</div>
        <h1 class="newsletter-page-title">Restez informé</h1>
        <p class="newsletter-page-sub">
            Recevez en avant-première nos nouveautés bio, les prochains jeux-concours
            et les actualités de la boutique Thé Tip Top.
        </p>

        <?php if ($success): ?>
            <div class="alert-ttt alert-ok">✅ Merci ! Votre inscription à la newsletter est confirmée.</div>
        <?php elseif ($error): ?>
            <div class="alert-ttt alert-erreur"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if (!$success): ?>
        <form method="POST">
            <input type="email" name="newsletter_email" class="newsletter-input" placeholder="votre@email.fr" required>
            <button type="submit" class="newsletter-btn-page">S'inscrire à la newsletter</button>
        </form>
        <?php endif; ?>

        <p style="font-size:0.72rem; color:#8a9a7a; margin-top:1.2rem;">Pas de spam, désinscription en un clic.</p>
    </div>
</div>

<style>.newsletter-section { display: none; }</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>