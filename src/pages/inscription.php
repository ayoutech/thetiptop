<?php
$page_title = 'Inscription — Thé Tip Top';
require_once __DIR__ . '/../includes/header.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';
    $age = intval($_POST['age'] ?? 0);
    $sexe = $_POST['sexe'] ?? 'autre';
    $newsletter = isset($_POST['newsletter']) ? 1 : 0;

    if (!$nom || !$prenom || !$email || !$password || !$age) {
        $error = 'Tous les champs obligatoires doivent être remplis.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Adresse email invalide.';
    } elseif (strlen($password) < 8) {
        $error = 'Le mot de passe doit contenir au moins 8 caractères.';
    } elseif ($password !== $confirm) {
        $error = 'Les mots de passe ne correspondent pas.';
    } elseif ($age < 18) {
        $error = 'Vous devez avoir au moins 18 ans pour participer.';
    } else {
        $db = getDB();
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'Cette adresse email est déjà utilisée.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO users (nom, prenom, email, password, age, sexe, newsletter) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$nom, $prenom, $email, $hash, $age, $sexe, $newsletter]);
            $userId = $db->lastInsertId();
            $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $_SESSION['user'] = $stmt->fetch();
            header('Location: /pages/participation.php?welcome=1');
            exit;
        }
    }
}
?>

<main class="py-5">
    <div class="container">
        <div class="form-card">
            <h2 class="text-center"><i class="bi bi-person-plus me-2"></i>Créer un compte</h2>
            <p class="text-center text-muted small mb-4">Inscrivez-vous pour participer au jeu-concours</p>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="row g-3">
                    <div class="col-6">
                        <label class="form-label fw-semibold">Prénom *</label>
                        <input type="text" name="prenom" class="form-control" required
                               value="<?= htmlspecialchars($_POST['prenom'] ?? '') ?>">
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-semibold">Nom *</label>
                        <input type="text" name="nom" class="form-control" required
                               value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Email *</label>
                        <input type="email" name="email" class="form-control" required
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-semibold">Âge *</label>
                        <input type="number" name="age" class="form-control" min="18" max="120" required
                               value="<?= htmlspecialchars($_POST['age'] ?? '') ?>">
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-semibold">Sexe</label>
                        <select name="sexe" class="form-select">
                            <option value="homme">Homme</option>
                            <option value="femme">Femme</option>
                            <option value="autre">Autre</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Mot de passe * (8 caractères min.)</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Confirmer le mot de passe *</label>
                        <input type="password" name="confirm" class="form-control" required>
                    </div>
                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="newsletter" id="newsletter">
                            <label class="form-check-label small" for="newsletter">
                                J'accepte de recevoir des offres et actualités de Thé Tip Top par email
                            </label>
                        </div>
                    </div>
                    <div class="col-12">
                        <p class="small text-muted">
                            En vous inscrivant, vous acceptez notre
                            <a href="/pages/confidentialite.php">politique de confidentialité</a>.
                            Vos données ne seront pas cédées à des tiers.
                        </p>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-tiptop w-100">
                            <i class="bi bi-person-check me-2"></i>Créer mon compte
                        </button>
                    </div>
                </div>
            </form>

            <hr>
            <p class="text-center small">
                Déjà inscrit ? <a href="/pages/connexion.php">Se connecter</a>
            </p>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
