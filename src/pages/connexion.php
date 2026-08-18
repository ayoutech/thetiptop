<?php
$page_title = 'Connexion — Thé Tip Top';
require_once __DIR__ . '/../includes/header.php';

if ($user) { header('Location: /pages/participation.php'); exit; }

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        $error = 'Veuillez remplir tous les champs.';
    } else {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $u = $stmt->fetch();
        if ($u && password_verify($password, $u['password'])) {
            $_SESSION['user'] = $u;
            $redirect = $_GET['redirect'] ?? '/pages/participation.php';
            header('Location: ' . $redirect);
            exit;
        } else {
            $error = 'Email ou mot de passe incorrect.';
        }
    }
}
?>

<main class="py-5">
    <div class="container">
        <div class="form-card">
            <h2 class="text-center"><i class="bi bi-box-arrow-in-right me-2"></i>Connexion</h2>
            <p class="text-center text-muted small mb-4">Connectez-vous pour participer au jeu-concours</p>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Email</label>
                    <input type="email" name="email" class="form-control" required
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>
                <div class="mb-4">
                    <label class="form-label fw-semibold">Mot de passe</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-tiptop w-100">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Se connecter
                </button>
            </form>

            <hr>
            <p class="text-center small">
                Pas encore de compte ? <a href="/pages/inscription.php">S'inscrire gratuitement</a>
            </p>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
