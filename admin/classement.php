<?php
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/fonctions_admin.php';
$moi = exiger_role('admin');

$q = trim((string) ($_GET['q'] ?? ''));
$par_page = 25;
$page = max(1, (int) ($_GET['page'] ?? 1));

// Classement complet : rang calculé en PHP (ex aequo = même rang), puis filtre et pagination
$lignes = $pdo->query(
    "SELECT f.id_filleul, f.nom, f.prenom, f.nom_utilisateur, f.telephone, f.statut_compte, COUNT(s.id_parrain) AS nb
     FROM filleul f LEFT JOIN signature s ON s.id_filleul = f.id_filleul
     GROUP BY f.id_filleul, f.nom, f.prenom, f.nom_utilisateur, f.telephone, f.statut_compte
     ORDER BY nb DESC, f.nom, f.prenom"
)->fetchAll();
$rang = 0;
$precedent = null;
foreach ($lignes as $i => &$l) {
    if ($l['nb'] !== $precedent) {
        $rang = $i + 1;
        $precedent = $l['nb'];
    }
    $l['rang'] = $rang;
}
unset($l);
if ($q !== '') {
    $lignes = array_values(array_filter($lignes, fn($l) => stripos($l['nom'] . ' ' . $l['prenom'] . ' ' . $l['nom_utilisateur'] . ' ' . $l['telephone'], $q) !== false));
}
$total = count($lignes);
$nb_pages = max(1, (int) ceil($total / $par_page));
$page = min($page, $nb_pages);
$affichees = array_slice($lignes, ($page - 1) * $par_page, $par_page);

admin_debut('Classement', 'classement');
?>
<div class="admin-titre">
    <h1>Classement des DUT1 <span class="petit">(<?= e($total) ?>)</span></h1>
    <a class="btn btn-secondaire" href="<?= e(url('admin/telechargements.php?fichier=classement')) ?>"><?= admin_icone('telechargements', 18) ?> Télécharger</a>
</div>

<form class="carte admin-filtres admin-filtres-2" method="get">
    <div class="champ">
        <label for="q">Rechercher</label>
        <input id="q" name="q" value="<?= e($q) ?>" placeholder="Nom, prénom, identifiant ou téléphone">
    </div>
    <button class="btn">Rechercher</button>
</form>

<?php if (!$affichees): ?>
    <p class="carte petit">Aucun DUT1 ne correspond.</p>
<?php else: ?>
    <div class="carte admin-tableau-defilant">
        <table class="tableau">
            <thead><tr><th>Rang</th><th>Nom</th><th>Prénom</th><th>Téléphone</th><th>Signatures</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($affichees as $l): ?>
                <tr>
                    <td><span class="admin-rang <?= $l['rang'] <= 3 ? 'admin-rang-' . $l['rang'] : '' ?>"><?= e($l['rang']) ?></span></td>
                    <td><?= e($l['nom']) ?></td>
                    <td><?= e($l['prenom']) ?><?= $l['statut_compte'] === 'suspendu' ? ' <span class="admin-badge admin-badge-suspendu">suspendu</span>' : '' ?></td>
                    <td><a href="<?= e(lien_whatsapp($l['telephone'])) ?>" target="_blank" rel="noopener"><?= e($l['telephone']) ?></a></td>
                    <td><strong><?= e($l['nb']) ?></strong></td>
                    <td class="admin-actions">
                        <a href="<?= e(admin_url('utilisateur_voir.php', 'dut1', (int) $l['id_filleul'])) ?>">Voir</a>
                        <a href="<?= e(url('admin/signatures.php?dut1=' . $l['id_filleul'])) ?>">Signatures</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php if ($nb_pages > 1): ?>
        <nav class="admin-pagination">
            <?php for ($p = 1; $p <= $nb_pages; $p++): ?>
                <a class="<?= $p === $page ? 'actif' : '' ?>" href="<?= e(url('admin/classement.php?' . http_build_query(['q' => $q, 'page' => $p]))) ?>"><?= $p ?></a>
            <?php endfor; ?>
        </nav>
    <?php endif; ?>
<?php endif; ?>
<?php admin_fin(); ?>
