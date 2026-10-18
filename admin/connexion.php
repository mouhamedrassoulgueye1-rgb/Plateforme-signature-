<?php
require_once __DIR__ . '/../includes/init.php';

// Déjà connecté en admin : direction le tableau de bord
$u = utilisateur();
if ($u && $u['role'] === 'admin') {
    rediriger('admin/index.php');
}

// Limite les essais : 5 échecs → 5 minutes d'attente (compteur dans la session)
const ESSAIS_MAX = 5;
const ATTENTE_SECONDES = 300;

$erreur = '';
$bloque_jusqua = $_SESSION['admin_bloque_jusqua'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifier_csrf();
    $nom_utilisateur = normaliser_nom_utilisateur($_POST['nom_utilisateur'] ?? '');
    $mot_de_passe = (string) ($_POST['mot_de_passe'] ?? '');

    if (time() < $bloque_jusqua) {
        $erreur = 'Trop d’essais. Réessayez dans ' . ceil(($bloque_jusqua - time()) / 60) . ' minute(s).';
    } elseif ($nom_utilisateur === '' || $mot_de_passe === '') {
        $erreur = 'Remplissez les deux champs.';
    } else {
        $admin = verifier_identifiants($pdo, 'admin', $nom_utilisateur, $mot_de_passe);
        if ($admin) {
            unset($_SESSION['admin_essais'], $_SESSION['admin_bloque_jusqua']);
            ouvrir_session('admin', $admin);
            flash('succes', 'Bienvenue, ' . $admin['prenom'] . '.');
            rediriger('admin/index.php');
        }
        $_SESSION['admin_essais'] = ($_SESSION['admin_essais'] ?? 0) + 1;
        if ($_SESSION['admin_essais'] >= ESSAIS_MAX) {
            $_SESSION['admin_bloque_jusqua'] = time() + ATTENTE_SECONDES;
            $_SESSION['admin_essais'] = 0;
            $erreur = 'Trop d’essais. Réessayez dans 5 minutes.';
        } else {
            $erreur = 'Nom d’utilisateur ou mot de passe incorrect.';
        }
    }
}

$titre_page = 'Connexion administrateur';
require __DIR__ . '/../includes/header.php';
?>
<section class="carte admin-connexion">
    <h1><?= e($titre_page) ?></h1>
    <?php if ($erreur): ?>
        <div class="alerte alerte-erreur"><?= e($erreur) ?></div>
    <?php endif; ?>
    <form class="formulaire" method="post" autocomplete="off">
        <?= champ_csrf() ?>
        <div class="champ">
            <label for="nom_utilisateur">Nom d’utilisateur</label>
            <input id="nom_utilisateur" name="nom_utilisateur" required maxlength="30" autofocus
                   value="<?= e($_POST['nom_utilisateur'] ?? '') ?>">
        </div>
        <div class="champ">
            <label for="mot_de_passe">Mot de passe</label>
            <input id="mot_de_passe" name="mot_de_passe" type="password" required>
        </div>
        <button class="btn">Se connecter</button>
    </form>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
