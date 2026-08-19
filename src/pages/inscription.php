<?php ob_start();
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: /pages/mon-compte.php');
    exit;
}
require_once __DIR__ . '/../config/database.php';
$pdo = getDB();
$erreur = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $prenom   = trim($_POST['prenom'] ?? '');
    $nom      = trim($_POST['nom'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $age      = intval($_POST['age'] ?? 0);
    $sexe     = $_POST['sexe'] ?? '';
    $mdp      = $_POST['mot_de_passe'] ?? '';
    $mdp2     = $_POST['mot_de_passe2'] ?? '';
    $newsletter = isset($_POST['newsletter']) ? 1 : 0;

    if (!$prenom || !$nom || !$email || !$age || !$sexe || !$mdp) {
        $erreur = 'Tous les champs obligatoires doivent être remplis.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreur = 'Adresse email invalide.';
    } elseif ($age < 18) {
        $erreur = 'Vous devez avoir au moins 18 ans pour participer.';
    } elseif (strlen($mdp) < 8) {
        $erreur = 'Le mot de passe doit contenir au moins 8 caractères.';
    } elseif ($mdp !== $mdp2) {
        $erreur = 'Les mots de passe ne correspondent pas.';
    } else {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $erreur = 'Cette adresse email est déjà utilisée.';
        } else {
            $hash = password_hash($mdp, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO users (prenom, nom, email, age, sexe, mot_de_passe, newsletter, role) VALUES (?,?,?,?,?,?,?,?)');
            $stmt->execute([$prenom, $nom, $email, $age, $sexe, $hash, $newsletter, 'client']);
            $user_id = $pdo->lastInsertId();
            $_SESSION['user_id']  = $user_id;
            $_SESSION['user_nom'] = $prenom . ' ' . $nom;
            $_SESSION['user_role'] = 'client';
            header('Location: /pages/participation.php');
            exit;
        }
    }
}

$page_title = 'Créer un compte — Thé Tip Top';
require_once __DIR__ . '/../includes/header.php';
?>

<style>
.auth-section {
    min-height: calc(100vh - 68px);
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--blanc-casse);
    padding: 3rem 1.5rem;
}
.auth-card {
    background: #fff;
    border: 1px solid rgba(45,74,45,0.1);
    border-radius: 4px;
    padding: 3rem 2.5rem;
    width: 100%;
    max-width: 520px;
    box-shadow: 0 4px 32px rgba(26,46,26,0.06);
}
.auth-logo {
    text-align: center;
    margin-bottom: 2rem;
}
.auth-logo-sym {
    font-family: 'Playfair Display', serif;
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--vert-nuit);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}
.auth-logo-sym span { color: var(--or); font-size: 1.3rem; }
.auth-title {
    font-family: 'Playfair Display', serif;
    font-size: 1.6rem;
    font-weight: 700;
    color: var(--vert-nuit);
    margin-bottom: 0.3rem;
    text-align: center;
}
.auth-sub {
    font-size: 0.83rem;
    color: #6a7f6a;
    text-align: center;
    margin-bottom: 2rem;
}
.auth-divider {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 1.8rem;
}
.auth-divider::before, .auth-divider::after {
    content: '';
    flex: 1;
    height: 1px;
    background: rgba(45,74,45,0.12);
}
.auth-divider span {
    font-size: 0.68rem;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: #9aaa9a;
}
.form-group {
    margin-bottom: 1.1rem;
}
.form-label {
    display: block;
    font-size: 0.78rem;
    font-weight: 600;
    color: var(--vert-nuit);
    margin-bottom: 0.4rem;
    letter-spacing: 0.02em;
}
.form-label .req { color: var(--or); margin-left: 2px; }
.form-control-ttt {
    width: 100%;
    padding: 11px 14px;
    border: 1px solid rgba(45,74,45,0.2);
    border-radius: 2px;
    font-size: 0.88rem;
    font-family: 'Inter', sans-serif;
    color: var(--vert-nuit);
    background: #fff;
    outline: none;
    transition: border-color 0.2s, box-shadow 0.2s;
    -webkit-appearance: none;
}
.form-control-ttt:focus {
    border-color: var(--or);
    box-shadow: 0 0 0 3px rgba(201,168,76,0.12);
}
.form-control-ttt::placeholder { color: #b0c0b0; }
.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}
.form-row-3 {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 12px;
}
.form-check-ttt {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    margin-bottom: 0.8rem;
}
.form-check-ttt input[type="checkbox"] {
    width: 16px;
    height: 16px;
    margin-top: 2px;
    accent-color: var(--or);
    flex-shrink: 0;
    cursor: pointer;
}
.form-check-ttt label {
    font-size: 0.78rem;
    color: #6a7f6a;
    line-height: 1.5;
    cursor: pointer;
}
.form-check-ttt label a { color: var(--or); text-decoration: none; }
.btn-submit-ttt {
    width: 100%;
    background: var(--vert-nuit);
    color: var(--creme);
    padding: 13px;
    border: none;
    border-radius: 2px;
    font-size: 0.82rem;
    font-weight: 700;
    letter-spacing: 0.09em;
    text-transform: uppercase;
    cursor: pointer;
    transition: background 0.2s;
    margin-top: 0.5rem;
}
.btn-submit-ttt:hover { background: var(--or); color: var(--vert-nuit); }
.auth-footer {
    text-align: center;
    margin-top: 1.5rem;
    font-size: 0.8rem;
    color: #6a7f6a;
}
.auth-footer a { color: var(--or); text-decoration: none; font-weight: 600; }
.alert-ttt {
    padding: 12px 16px;
    border-radius: 2px;
    font-size: 0.82rem;
    margin-bottom: 1.5rem;
}
.alert-erreur {
    background: #fdf0f0;
    border: 1px solid #f0c0c0;
    color: #8b2020;
}
.alert-ok {
    background: #f0fdf0;
    border: 1px solid #c0e0c0;
    color: #1a4a1a;
}
@media (max-width: 480px) {
    .auth-card { padding: 2rem 1.2rem; }
    .form-row, .form-row-3 { grid-template-columns: 1fr; }
}
</style>

<section class="auth-section">
    <div class="auth-card">
        <div class="auth-logo">
            <div class="auth-logo-sym"><span>☽</span> Thé Tip Top</div>
        </div>

        <h1 class="auth-title">Créer un compte</h1>
        <p class="auth-sub">Inscrivez-vous pour participer au jeu-concours</p>

        <?php if ($erreur): ?>
            <div class="alert-ttt alert-erreur"><?= htmlspecialchars($erreur) ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Prénom <span class="req">*</span></label>
                    <input type="text" name="prenom" class="form-control-ttt"
                           placeholder="Marie" required
                           value="<?= htmlspecialchars($_POST['prenom'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Nom <span class="req">*</span></label>
                    <input type="text" name="nom" class="form-control-ttt"
                           placeholder="Dupont" required
                           value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Adresse email <span class="req">*</span></label>
                <input type="email" name="email" class="form-control-ttt"
                       placeholder="marie@exemple.fr" required
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Âge <span class="req">*</span></label>
                    <input type="number" name="age" class="form-control-ttt"
                           placeholder="25" min="18" max="120" required
                           value="<?= htmlspecialchars($_POST['age'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Sexe <span class="req">*</span></label>
                    <select name="sexe" class="form-control-ttt" required>
                        <option value="">Choisir</option>
                        <option value="Homme" <?= ($_POST['sexe'] ?? '') === 'Homme' ? 'selected' : '' ?>>Homme</option>
                        <option value="Femme" <?= ($_POST['sexe'] ?? '') === 'Femme' ? 'selected' : '' ?>>Femme</option>
                        <option value="Autre" <?= ($_POST['sexe'] ?? '') === 'Autre' ? 'selected' : '' ?>>Autre</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Mot de passe <span class="req">*</span></label>
                <input type="password" name="mot_de_passe" class="form-control-ttt"
                       placeholder="8 caractères minimum" required minlength="8">
            </div>

            <div class="form-group">
                <label class="form-label">Confirmer le mot de passe <span class="req">*</span></label>
                <input type="password" name="mot_de_passe2" class="form-control-ttt"
                       placeholder="Répétez votre mot de passe" required>
            </div>

            <div class="form-check-ttt">
                <input type="checkbox" name="newsletter" id="newsletter">
                <label for="newsletter">
                    J'accepte de recevoir les offres et actualités de Thé Tip Top par email
                </label>
            </div>

            <div class="form-check-ttt">
                <input type="checkbox" id="rgpd" required>
                <label for="rgpd">
                    En m'inscrivant, j'accepte la
                    <a href="/pages/confidentialite.php">politique de confidentialité</a>.
                    Vos données ne seront pas cédées à des tiers. <span class="req">*</span>
                </label>
            </div>

            <button type="submit" class="btn-submit-ttt">Créer mon compte</button>
        </form>

        <div class="auth-footer">
            Déjà inscrit ? <a href="/pages/connexion.php">Se connecter</a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
