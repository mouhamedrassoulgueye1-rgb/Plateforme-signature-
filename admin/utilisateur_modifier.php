<?php
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/fonctions_admin.php';
$moi = exiger_role('admin');

$c = admin_lire_cible($pdo);
$r = ROLES[$c['role']];
$erreurs = [];
$donnees = $c; // valeurs affichées dans le formulaire

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifier_csrf();
    [$v, $erreurs] = admin_valider_compte($pdo, $c['role'], $_POST, $c['nom_utilisateur']);
    $v['statut_compte'] = ($_POST['statut_compte'] ?? '') === 'suspendu' ? 'suspendu' : 'actif';
    $donnees = array_merge($c, $_POST);

    $photo = $c['photo'];
    $ancienne_photo = null;
    if (!$erreurs) {
        try {
            $nouvelle = enregistrer_photo($_FILES['photo'] ?? null, $c['role']);
            if ($nouvelle !== null) {
                $ancienne_photo = $photo;
                $photo = $nouvelle;
            } elseif (!empty($_POST['retirer_photo'])) {
                $ancienne_photo = $photo;
                $photo = null;
            }
        } catch (RuntimeException $ex) {
            $erreurs['photo'] = $ex->getMessage();
        }
    }

    if (!$erreurs) {
        $colonnes = ['nom', 'prenom', 'nom_utilisateur', 'telephone', 'statut_compte'];
        if ($c['role'] === 'parrain') {
            array_push($colonnes, 'type_parrain', 'chambre');
        }
        $valeurs = array_map(fn($col) => $v[$col], $colonnes);
        $colonnes[] = 'photo';
        $valeurs[] = $photo;
        $valeurs[] = $c['id'];
        $set = implode(', ', array_map(fn($col) => $col . ' = ?', $colonnes));
        $pdo->prepare('UPDATE ' . $r['table'] . ' SET ' . $set . ' WHERE ' . $r['id'] . ' = ?')->execute($valeurs);
        admin_supprimer_photo($ancienne_photo);

        $message = 'Compte mis à jour.';
        if ($v['statut_compte'] !== $c['statut_compte']) {
            $message = $v['statut_compte'] === 'suspendu'
                ? 'Compte suspendu : cette personne ne peut plus se connecter ni signer.'
                : 'Compte réactivé.';
        }
        flash('succes', $message);
        header('Location: ' . admin_url('utilisateur_voir.php', $c['role'], $c['id']));
        exit;
    }
}

$titre_page = 'Modifier ' . $c['prenom'] . ' ' . $c['nom'];
require __DIR__ . '/../includes/header.php';
?>
<p><a href="<?= e(admin_url('utilisateur_voir.php', $c['role'], $c['id'])) ?>">← Fiche</a></p>
<section class="carte">
    <h1><?= e($titre_page) ?></h1>
    <?php if ($erreurs): ?>
        <div class="alerte alerte-erreur">Corrigez les champs signalés.</div>
    <?php endif; ?>
    <form class="formulaire" method="post" enctype="multipart/form-data" autocomplete="off">
        <?= champ_csrf() ?>
        <input type="hidden" name="role" value="<?= e($c['role']) ?>">
        <input type="hidden" name="id" value="<?= e($c['id']) ?>">
        <div class="champ">
            <label for="statut_compte">Statut du compte</label>
            <select id="statut_compte" name="statut_compte">
                <option value="actif" <?= $donnees['statut_compte'] === 'actif' ? 'selected' : '' ?>>Actif</option>
                <option value="suspendu" <?= $donnees['statut_compte'] === 'suspendu' ? 'selected' : '' ?>>Suspendu (ne peut plus se connecter ni signer)</option>
            </select>
        </div>
        <?= admin_champ('nom', 'Nom', (string) $donnees['nom'], $erreurs, 'required maxlength="50"') ?>
        <?= admin_champ('prenom', 'Prénom', (string) $donnees['prenom'], $erreurs, 'required maxlength="80"') ?>
        <?= admin_champ('nom_utilisateur', 'Nom d’utilisateur', (string) $donnees['nom_utilisateur'], $erreurs, 'required maxlength="30"') ?>
        <?= admin_champ('telephone', 'Téléphone (WhatsApp)', (string) $donnees['telephone'], $erreurs, 'required type="tel"') ?>
        <?php if ($c['role'] === 'parrain'): ?>
            <?= admin_choix_type((string) $donnees['type_parrain'], $erreurs) ?>
            <?= admin_champ('chambre', 'Chambre', (string) $donnees['chambre'], $erreurs, 'required maxlength="30"') ?>
        <?php endif; ?>
        <div class="champ">
            <label for="photo">Nouvelle photo (facultative)</label>
            <?php if ($c['photo']): ?>
                <img class="avatar" src="<?= e(url($c['photo'])) ?>" alt="Photo actuelle">
                <label class="petit"><input type="checkbox" name="retirer_photo" value="1"> Retirer la photo actuelle</label>
            <?php endif; ?>
            <input id="photo" name="photo" type="file" accept="image/jpeg,image/png,image/webp">
            <?php if (isset($erreurs['photo'])): ?><span class="admin-erreur-champ"><?= e($erreurs['photo']) ?></span><?php endif; ?>
        </div>
        <button class="btn">Enregistrer</button>
    </form>
    <p class="petit">Pour changer le mot de passe : <a href="<?= e(admin_url('mot_de_passe.php', $c['role'], $c['id'])) ?>">nouveau mot de passe</a>.</p>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
