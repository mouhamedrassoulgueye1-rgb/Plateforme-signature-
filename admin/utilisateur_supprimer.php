<?php
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/fonctions_admin.php';
$moi = exiger_role('admin');

$c = admin_lire_cible($pdo);
$r = ROLES[$c['role']];
$nb = $c['role'] === 'dut1' ? nb_signatures_filleul($pdo, $c['id']) : nb_signatures_parrain($pdo, $c['id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifier_csrf();
    if (normaliser_nom_utilisateur((string) ($_POST['confirmation'] ?? '')) !== $c['nom_utilisateur']) {
        flash('erreur', 'Recopiez exactement le nom d’utilisateur pour confirmer.');
        header('Location: ' . admin_url('utilisateur_supprimer.php', $c['role'], $c['id']));
        exit;
    }
    // Les signatures liées sont effacées par la base (ON DELETE CASCADE)
    $pdo->prepare('DELETE FROM ' . $r['table'] . ' WHERE ' . $r['id'] . ' = ?')->execute([$c['id']]);
    admin_supprimer_photo($c['photo']);
    flash('succes', 'Compte de ' . $c['prenom'] . ' ' . $c['nom'] . ' supprimé, ainsi que ses ' . $nb . ' signature(s).');
    rediriger('admin/utilisateurs.php');
}

$titre_page = 'Supprimer ' . $c['prenom'] . ' ' . $c['nom'];
require __DIR__ . '/../includes/header.php';
?>
<p><a href="<?= e(admin_url('utilisateur_voir.php', $c['role'], $c['id'])) ?>">← Fiche</a></p>
<section class="carte admin-danger">
    <h1><?= e($titre_page) ?></h1>
    <p>Cette action est <strong>définitive</strong>. Elle efface le compte <strong><?= e($c['nom_utilisateur']) ?></strong>
        et ses <strong><?= e($nb) ?> signature(s) <?= $c['role'] === 'dut1' ? 'reçues' : 'données' ?></strong>.</p>
    <p>Pour empêcher temporairement quelqu’un d’utiliser le site, préférez
        <a href="<?= e(admin_url('utilisateur_modifier.php', $c['role'], $c['id'])) ?>">suspendre le compte</a>.</p>
    <form class="formulaire" method="post">
        <?= champ_csrf() ?>
        <input type="hidden" name="role" value="<?= e($c['role']) ?>">
        <input type="hidden" name="id" value="<?= e($c['id']) ?>">
        <div class="champ">
            <label for="confirmation">Recopiez « <?= e($c['nom_utilisateur']) ?> » pour confirmer</label>
            <input id="confirmation" name="confirmation" required autocomplete="off">
        </div>
        <button class="btn btn-danger">Supprimer définitivement</button>
    </form>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
