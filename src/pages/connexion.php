<?php ob_start();
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: /pages/mon-compte.php');
    exit;
}
require_once __DIR__ . '/../config/database.php';
$pdo = getDB();
$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $mdp   = $_POST['mot_de_passe'] ?? '';

    if (!$email || !$mdp) {
        $erreur = 'Veuillez remplir tous les champs.';
    } else {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($mdp, $user['password'])) {
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_nom']  = $user['prenom'] . ' ' . $user['nom'];
            $_SESSION['user_role'] = $user['role'];

            if ($user['role'] === 'admin') {
                header('Location: /pages/admin.php');
            } elseif ($user['role'] === 'employe') {
                header('Location: /pages/employe.php');
            } else {
                header('Location: /pages/mon-compte.php');
            }
            exit;
        } else {
            $erreur = 'Email ou mot de passe incorrect.';
        }
    }
}

$page_title = 'Connexion — Thé Tip Top';
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
    max-width: 420px;
    box-shadow: 0 4px 32px rgba(26,46,26,0.06);
}
.auth-logo { text-align: center; margin-bottom: 2rem; }
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
.form-group { margin-bottom: 1.1rem; }
.form-label {
    display: block;
    font-size: 0.78rem;
    font-weight: 600;
    color: var(--vert-nuit);
    margin-bottom: 0.4rem;
    letter-spacing: 0.02em;
}
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
@media (max-width: 480px) {
    .auth-card { padding: 2rem 1.2rem; }
}
</style>

<section class="auth-section">
    <div class="auth-card">
        <div class="auth-logo">
            <div class="auth-logo-sym"><span>☽</span> Thé Tip Top</div>
        </div>

        <h1 class="auth-title">Connexion</h1>
        <p class="auth-sub">Accédez à votre espace participant</p>

        <?php if ($erreur): ?>
            <div class="alert-ttt alert-erreur"><?= htmlspecialchars($erreur) ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label class="form-label">Adresse email</label>
                <input type="email" name="email" class="form-control-ttt"
                       placeholder="marie@exemple.fr" required
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label class="form-label">Mot de passe</label>
                <input type="password" name="mot_de_passe" class="form-control-ttt"
                       placeholder="Votre mot de passe" required>
            </div>

            <button type="submit" class="btn-submit-ttt">Se connecter</button>
        </form>

        <div class="auth-footer">
            Pas encore de compte ? <a href="/pages/inscription.php">S'inscrire gratuitement</a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>