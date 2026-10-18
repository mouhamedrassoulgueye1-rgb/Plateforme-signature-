<?php
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/fonctions_admin.php';
$moi = exiger_role('admin');

// Filtres : ?role=tous|dut1|parrain  &statut=tous|actif|suspendu  &q=recherche
$role = in_array($_GET['role'] ?? '', ['dut1', 'parrain'], true) ? $_GET['role'] : 'tous';
$statut = in_array($_GET['statut'] ?? '', ['actif', 'suspendu'], true) ? $_GET['statut'] : 'tous';
$q = trim((string) ($_GET['q'] ?? ''));

/** Lit les comptes d'une table avec les filtres. $compte_signatures : colonne de signature à compter. */
function lire_comptes(PDO $pdo, string $role, string $statut, string $q): array
{
    $r = ROLES[$role];
    $extra = $role === 'parrain' ? ', u.type_parrain, u.chambre' : ", NULL AS type_parrain, NULL AS chambre";
    $sql = 'SELECT u.' . $r['id'] . ' AS id, u.nom, u.prenom, u.nom_utilisateur, u.telephone,
                   u.statut_compte, u.date_inscription' . $extra . ',
                   (SELECT COUNT(*) FROM signature s WHERE s.' . $r['id'] . ' = u.' . $r['id'] . ') AS nb_signatures
            FROM ' . $r['table'] . ' u WHERE 1 = 1';
    $params = [];
    if ($statut !== 'tous') {
        $sql .= ' AND u.statut_compte = :statut';
        $params['statut'] = $statut;
    }
    if ($q !== '') {
        $sql .= ' AND (u.nom LIKE :q1 OR u.prenom LIKE :q2 OR u.nom_utilisateur LIKE :q3 OR u.telephone LIKE :q4)';
        $motif = '%' . addcslashes($q, '%_\\') . '%';
        $params += ['q1' => $motif, 'q2' => $motif, 'q3' => $motif, 'q4' => $motif];
    }
    $req = $pdo->prepare($sql . ' ORDER BY u.nom, u.prenom');
    $req->execute($params);
    $lignes = $req->fetchAll();
    foreach ($lignes as &$l) {
        $l['role'] = $role;
    }
    return $lignes;
}

$comptes = [];
foreach (['dut1', 'parrain'] as $r) {
    if ($role === 'tous' || $role === $r) {
        $comptes = array_merge($comptes, lire_comptes($pdo, $r, $statut, $q));
    }
}
usort($comptes, fn($a, $b) => [$a['nom'], $a['prenom']] <=> [$b['nom'], $b['prenom']]);

$libelles_role = ['dut1' => 'DUT1', 'parrain' => 'Parrain'];

$titre_page = 'Utilisateurs';
admin_debut($titre_page, 'utilisateurs');
?>
<div class="admin-titre">
    <h1><?= e($titre_page) ?> <span class="petit">(<?= e(count($comptes)) ?>)</span></h1>
    <a class="btn" href="<?= e(url('admin/utilisateur_creer.php')) ?>">Créer un utilisateur</a>
</div>

<form class="carte admin-filtres" method="get">
    <div class="champ">
        <label for="q">Rechercher</label>
        <input id="q" name="q" value="<?= e($q) ?>" placeholder="Nom, prénom, identifiant ou téléphone">
    </div>
    <div class="champ">
        <label for="role">Rôle</label>
        <select id="role" name="role">
            <?php foreach (['tous' => 'Tous', 'dut1' => 'DUT1', 'parrain' => 'Parrains, marraines, Mames'] as $v => $l): ?>
                <option value="<?= e($v) ?>" <?= $role === $v ? 'selected' : '' ?>><?= e($l) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="champ">
        <label for="statut">Statut</label>
        <select id="statut" name="statut">
            <?php foreach (['tous' => 'Tous', 'actif' => 'Actifs', 'suspendu' => 'Suspendus'] as $v => $l): ?>
                <option value="<?= e($v) ?>" <?= $statut === $v ? 'selected' : '' ?>><?= e($l) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <button class="btn">Filtrer</button>
</form>

<?php if (!$comptes): ?>
    <p class="carte petit">Aucun utilisateur ne correspond.</p>
<?php else: ?>
    <div class="admin-tableau-defilant">
        <table class="tableau">
            <thead>
            <tr><th>Nom</th><th>Identifiant</th><th>Rôle</th><th>Téléphone</th><th>Signatures</th><th>Statut</th><th></th></tr>
            </thead>
            <tbody>
            <?php foreach ($comptes as $c): ?>
                <tr>
                    <td><?= e($c['prenom'] . ' ' . $c['nom']) ?></td>
                    <td><?= e($c['nom_utilisateur']) ?></td>
                    <td>
                        <?= e($c['type_parrain'] ?? $libelles_role[$c['role']]) ?>
                        <?php if ($c['chambre']): ?><span class="petit">· ch. <?= e($c['chambre']) ?></span><?php endif; ?>
                    </td>
                    <td><a href="<?= e(lien_whatsapp($c['telephone'])) ?>" target="_blank" rel="noopener"><?= e($c['telephone']) ?></a></td>
                    <td><?= e($c['nb_signatures']) ?> <span class="petit"><?= $c['role'] === 'dut1' ? 'reçues' : 'données' ?></span></td>
                    <td><span class="admin-badge admin-badge-<?= e($c['statut_compte']) ?>"><?= e($c['statut_compte']) ?></span></td>
                    <td class="admin-actions">
                        <a href="<?= e(url('admin/utilisateur_voir.php?role=' . $c['role'] . '&id=' . $c['id'])) ?>">Voir</a>
                        <a href="<?= e(url('admin/utilisateur_modifier.php?role=' . $c['role'] . '&id=' . $c['id'])) ?>">Modifier</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
<?php admin_fin(); ?>
