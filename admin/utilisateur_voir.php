<?php
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/fonctions_admin.php';
$moi = exiger_role('admin');

$c = admin_lire_cible($pdo);
if ($c['role'] === 'dut1') {
    $liste = signataires_du_filleul($pdo, $c['id']);
    $titre_liste = 'Signatures reçues';
} else {
    $liste = filleuls_signes_par($pdo, $c['id']);
    $titre_liste = 'Signatures données';
}

$titre_page = $c['prenom'] . ' ' . $c['nom'];
require __DIR__ . '/../includes/header.php';
?>
<p><a href="<?= e(url('admin/utilisateurs.php')) ?>">← Utilisateurs</a></p>
<section class="carte admin-fiche">
    <?php if ($c['photo']): ?>
        <img class="admin-fiche-photo" src="<?= e(url($c['photo'])) ?>" alt="">
    <?php else: ?>
        <div class="admin-fiche-photo admin-fiche-initiales"><?= e(mb_substr($c['prenom'], 0, 1) . mb_substr($c['nom'], 0, 1)) ?></div>
    <?php endif; ?>
    <div>
        <h1><?= e($titre_page) ?></h1>
        <p>
            <span class="admin-badge admin-badge-<?= e($c['statut_compte']) ?>"><?= e($c['statut_compte']) ?></span>
            <?= e($c['role'] === 'dut1' ? 'DUT1' : $c['type_parrain']) ?>
        </p>
        <dl class="admin-infos">
            <dt>Nom d’utilisateur</dt><dd><?= e($c['nom_utilisateur']) ?></dd>
            <dt>Téléphone</dt><dd><a href="<?= e(lien_whatsapp($c['telephone'])) ?>" target="_blank" rel="noopener"><?= e($c['telephone']) ?></a></dd>
            <?php if ($c['role'] === 'parrain'): ?><dt>Chambre</dt><dd><?= e($c['chambre']) ?></dd><?php endif; ?>
            <dt>Inscrit le</dt><dd><?= e(date('d/m/Y à H:i', strtotime($c['date_inscription']))) ?></dd>
            <?php if ($c['role'] === 'dut1'): ?>
                <dt>Rang</dt><dd><?= e(rang_filleul($pdo, $c['id'])) ?> sur <?= e(nb_filleuls_actifs($pdo)) ?></dd>
            <?php endif; ?>
        </dl>
    </div>
</section>

<nav class="admin-menu">
    <a class="btn" href="<?= e(admin_url('utilisateur_modifier.php', $c['role'], $c['id'])) ?>">Modifier / suspendre</a>
    <a class="btn btn-secondaire" href="<?= e(admin_url('mot_de_passe.php', $c['role'], $c['id'])) ?>">Nouveau mot de passe</a>
    <?php if ($c['role'] === 'dut1'): ?>
        <a class="btn btn-secondaire" href="<?= e(url('admin/signatures.php?dut1=' . $c['id'])) ?>">Gérer ses signatures</a>
    <?php endif; ?>
    <a class="btn btn-danger" href="<?= e(admin_url('utilisateur_supprimer.php', $c['role'], $c['id'])) ?>">Supprimer</a>
</nav>

<section class="carte">
    <h2><?= e($titre_liste) ?> (<?= e(count($liste)) ?>)</h2>
    <?php if (!$liste): ?>
        <p class="petit">Aucune signature pour l’instant.</p>
    <?php else: ?>
        <div class="admin-tableau-defilant">
            <table class="tableau">
                <thead><tr><th>Nom</th><?php if ($c['role'] === 'dut1'): ?><th>Type</th><th>Chambre</th><?php endif; ?><th>Téléphone</th><th>Date</th></tr></thead>
                <tbody>
                <?php foreach ($liste as $l): $autre = $c['role'] === 'dut1' ? ['parrain', $l['id_parrain']] : ['dut1', $l['id_filleul']]; ?>
                    <tr>
                        <td><a href="<?= e(admin_url('utilisateur_voir.php', $autre[0], (int) $autre[1])) ?>"><?= e($l['prenom'] . ' ' . $l['nom']) ?></a></td>
                        <?php if ($c['role'] === 'dut1'): ?><td><?= e($l['type_parrain']) ?></td><td><?= e($l['chambre']) ?></td><?php endif; ?>
                        <td><a href="<?= e(lien_whatsapp($l['telephone'])) ?>" target="_blank" rel="noopener"><?= e($l['telephone']) ?></a></td>
                        <td><?= e(date('d/m/Y H:i', strtotime($l['date_signature']))) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
