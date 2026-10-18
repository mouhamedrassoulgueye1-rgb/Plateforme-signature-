<?php
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/fonctions_admin.php';
$moi = exiger_role('admin');

$role = (string) ($_POST['role'] ?? $_GET['role'] ?? 'dut1');
if (!isset(ROLES_GERES[$role])) {
    $role = 'dut1';
}
$erreurs = [];
$v = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifier_csrf();
    [$v, $erreurs] = admin_valider_compte($pdo, $role, $_POST);
    if ($e = admin_erreur_mot_de_passe($_POST)) {
        $erreurs['mot_de_passe'] = $e;
    }
    if (PHOTO_OBLIGATOIRE[$role] && (($_FILES['photo']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE)) {
        $erreurs['photo'] = 'La photo est obligatoire pour un DUT1 : les parrains doivent pouvoir le reconnaître.';
    }
    $photo = null;
    if (!$erreurs) {
        try {
            $photo = enregistrer_photo($_FILES['photo'] ?? null, $role);
        } catch (RuntimeException $ex) {
            $erreurs['photo'] = $ex->getMessage();
        }
    }
    if (!$erreurs) {
        $hash = password_hash((string) $_POST['mot_de_passe'], PASSWORD_DEFAULT);
        if ($role === 'dut1') {
            $req = $pdo->prepare('INSERT INTO filleul (nom, prenom, nom_utilisateur, telephone, photo, mot_de_passe) VALUES (?, ?, ?, ?, ?, ?)');
            $req->execute([$v['nom'], $v['prenom'], $v['nom_utilisateur'], $v['telephone'], $photo, $hash]);
        } else {
            $req = $pdo->prepare('INSERT INTO parrain (nom, prenom, nom_utilisateur, telephone, type_parrain, chambre, photo, mot_de_passe) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
            $req->execute([$v['nom'], $v['prenom'], $v['nom_utilisateur'], $v['telephone'], $v['type_parrain'], $v['chambre'], $photo, $hash]);
        }
        flash('succes', 'Compte de ' . $v['prenom'] . ' ' . $v['nom'] . ' créé.');
        header('Location: ' . admin_url('utilisateur_voir.php', $role, (int) $pdo->lastInsertId()));
        exit;
    }
}

$titre_page = 'Créer un utilisateur';
admin_debut($titre_page, 'utilisateurs');
?>
<p><a href="<?= e(url('admin/utilisateurs.php')) ?>">← Utilisateurs</a></p>
<section class="carte">
    <h1><?= e($titre_page) ?></h1>
    <div class="admin-onglets">
        <?php foreach (ROLES_GERES as $r => $libelle): ?>
            <a class="<?= $r === $role ? 'actif' : '' ?>" href="<?= e(url('admin/utilisateur_creer.php?role=' . $r)) ?>"><?= e($libelle) ?></a>
        <?php endforeach; ?>
    </div>
    <?php if ($erreurs): ?>
        <div class="alerte alerte-erreur">Corrige les champs signalés.</div>
    <?php endif; ?>
    <form class="formulaire" method="post" enctype="multipart/form-data" autocomplete="off">
        <?= champ_csrf() ?>
        <input type="hidden" name="role" value="<?= e($role) ?>">
        <?= admin_champ('nom', 'Nom', $_POST['nom'] ?? '', $erreurs, 'required maxlength="50"') ?>
        <?= admin_champ('prenom', 'Prénom', $_POST['prenom'] ?? '', $erreurs, 'required maxlength="80"') ?>
        <?= admin_champ('nom_utilisateur', 'Nom d’utilisateur', $_POST['nom_utilisateur'] ?? '', $erreurs, 'required maxlength="30"') ?>
        <?= admin_champ('telephone', 'Téléphone (WhatsApp)', $_POST['telephone'] ?? '', $erreurs, 'required type="tel" placeholder="77 123 45 67"') ?>
        <?php if ($role === 'parrain'): ?>
            <?= admin_choix_type($_POST['type_parrain'] ?? '', $erreurs) ?>
            <?= admin_champ('chambre', 'Chambre', $_POST['chambre'] ?? '', $erreurs, 'required maxlength="30"') ?>
        <?php endif; ?>
        <?= admin_champ('mot_de_passe', 'Mot de passe (8 caractères au moins)', '', $erreurs, 'type="password" required minlength="8"') ?>
        <?= admin_champ('confirmation_mot_de_passe', 'Confirmation du mot de passe', '', $erreurs, 'type="password" required minlength="8"') ?>
        <div class="champ">
            <label for="photo">Photo <?= PHOTO_OBLIGATOIRE[$role] ? '(obligatoire' : '(facultative' ?>, JPG, PNG ou WebP, 2 Mo au plus)</label>
            <input id="photo" name="photo" type="file" accept="image/jpeg,image/png,image/webp" <?= PHOTO_OBLIGATOIRE[$role] ? 'required' : '' ?>>
            <?php if (isset($erreurs['photo'])): ?><span class="admin-erreur-champ"><?= e($erreurs['photo']) ?></span><?php endif; ?>
        </div>
        <button class="btn">Créer le compte</button>
    </form>
</section>
<?php admin_fin(); ?>
