<?php
/**
 * Nouveau mot de passe.
 *   ?role=dut1|parrain&id=… : l'admin donne un nouveau mot de passe à quelqu'un qui a oublié le sien.
 *   sans paramètre          : l'admin change son propre mot de passe (l'actuel est demandé).
 */
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/fonctions_admin.php';
$moi = exiger_role('admin');

$pour_moi = !isset($_GET['role']) && !isset($_POST['role']);
$c = $pour_moi ? null : admin_lire_cible($pdo);
$erreur = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifier_csrf();
    $erreur = admin_erreur_mot_de_passe($_POST);
    if (!$erreur && $pour_moi && !verifier_identifiants($pdo, 'admin', $moi['nom_utilisateur'], (string) ($_POST['mot_de_passe_actuel'] ?? ''))) {
        $erreur = 'Le mot de passe actuel est incorrect.';
    }
    if (!$erreur) {
        $hash = password_hash((string) $_POST['mot_de_passe'], PASSWORD_DEFAULT);
        if ($pour_moi) {
            $pdo->prepare('UPDATE administrateur SET mot_de_passe = ? WHERE id_admin = ?')->execute([$hash, $moi['id']]);
            flash('succes', 'Ton mot de passe a été changé.');
            rediriger('admin/index.php');
        }
        $r = ROLES[$c['role']];
        $pdo->prepare('UPDATE ' . $r['table'] . ' SET mot_de_passe = ? WHERE ' . $r['id'] . ' = ?')->execute([$hash, $c['id']]);
        flash('succes', 'Nouveau mot de passe enregistré. Transmets-le à ' . $c['prenom'] . ' en privé (WhatsApp), et demande-lui de ne le donner à personne.');
        header('Location: ' . admin_url('utilisateur_voir.php', $c['role'], $c['id']));
        exit;
    }
}

$titre_page = $pour_moi ? 'Changer mon mot de passe' : 'Nouveau mot de passe pour ' . $c['prenom'] . ' ' . $c['nom'];
admin_debut($titre_page, 'parametres');
?>
<p><a href="<?= e($pour_moi ? url('admin/index.php') : admin_url('utilisateur_voir.php', $c['role'], $c['id'])) ?>">← Retour</a></p>
<section class="carte">
    <h1><?= e($titre_page) ?></h1>
    <?php if ($erreur): ?>
        <div class="alerte alerte-erreur"><?= e($erreur) ?></div>
    <?php endif; ?>
    <form class="formulaire" method="post" autocomplete="off">
        <?= champ_csrf() ?>
        <?php if (!$pour_moi): ?>
            <input type="hidden" name="role" value="<?= e($c['role']) ?>">
            <input type="hidden" name="id" value="<?= e($c['id']) ?>">
        <?php else: ?>
            <div class="champ">
                <label for="mot_de_passe_actuel">Mot de passe actuel</label>
                <input id="mot_de_passe_actuel" name="mot_de_passe_actuel" type="password" required>
            </div>
        <?php endif; ?>
        <div class="champ">
            <label for="mot_de_passe">Nouveau mot de passe (8 caractères au moins)</label>
            <input id="mot_de_passe" name="mot_de_passe" type="password" required minlength="8">
        </div>
        <div class="champ">
            <label for="confirmation_mot_de_passe">Confirmation</label>
            <input id="confirmation_mot_de_passe" name="confirmation_mot_de_passe" type="password" required minlength="8">
        </div>
        <button class="btn">Enregistrer</button>
    </form>
</section>
<?php admin_fin(); ?>
