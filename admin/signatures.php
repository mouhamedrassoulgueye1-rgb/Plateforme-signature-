<?php
/**
 * Gestion des signatures d'un DUT1 : ?dut1=<id_filleul>
 * L'admin peut ajouter une signature oubliée (même fonction que le reste du site) ou en retirer une (erreur, fraude).
 */
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/fonctions_admin.php';
$moi = exiger_role('admin');

$id_filleul = (int) ($_POST['dut1'] ?? $_GET['dut1'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $id_filleul > 0) {
    verifier_csrf();
    $id_parrain = (int) ($_POST['id_parrain'] ?? 0);
    if (($_POST['action'] ?? '') === 'ajouter') {
        $resultat = enregistrer_signature($pdo, $id_parrain, $id_filleul);
        flash($resultat === 'ok' ? 'succes' : 'erreur', $resultat === 'ok' ? 'Signature ajoutée.' : MESSAGES_SIGNATURE[$resultat]);
    } elseif (($_POST['action'] ?? '') === 'retirer') {
        $req = $pdo->prepare('DELETE FROM signature WHERE id_parrain = ? AND id_filleul = ?');
        $req->execute([$id_parrain, $id_filleul]);
        flash($req->rowCount() ? 'succes' : 'erreur', $req->rowCount() ? 'Signature retirée.' : 'Cette signature n’existe pas.');
    }
    rediriger('admin/signatures.php?dut1=' . $id_filleul);
}

$filleuls = $pdo->query('SELECT id_filleul, prenom, nom, nom_utilisateur FROM filleul ORDER BY nom, prenom')->fetchAll();
$dut1 = null;
foreach ($filleuls as $f) {
    if ((int) $f['id_filleul'] === $id_filleul) {
        $dut1 = $f;
    }
}
if ($dut1) {
    $signatures = signataires_du_filleul($pdo, $id_filleul);
    // Signataires actifs qui n'ont pas encore signé pour ce DUT1
    $req = $pdo->prepare(
        "SELECT p.id_parrain, p.prenom, p.nom, p.type_parrain, p.chambre FROM parrain p
         WHERE p.statut_compte = 'actif'
           AND NOT EXISTS (SELECT 1 FROM signature s WHERE s.id_parrain = p.id_parrain AND s.id_filleul = ?)
         ORDER BY p.nom, p.prenom"
    );
    $req->execute([$id_filleul]);
    $possibles = $req->fetchAll();
}

$titre_page = 'Gérer les signatures';
require __DIR__ . '/../includes/header.php';
?>
<p><a href="<?= e(url('admin/index.php')) ?>">← Tableau de bord</a></p>
<h1><?= e($titre_page) ?></h1>

<form class="carte admin-filtres admin-filtres-2" method="get">
    <div class="champ">
        <label for="dut1">DUT1</label>
        <select id="dut1" name="dut1" required>
            <option value="">Choisir un DUT1…</option>
            <?php foreach ($filleuls as $f): ?>
                <option value="<?= e($f['id_filleul']) ?>" <?= (int) $f['id_filleul'] === $id_filleul ? 'selected' : '' ?>>
                    <?= e($f['nom'] . ' ' . $f['prenom'] . ' (' . $f['nom_utilisateur'] . ')') ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <button class="btn">Afficher</button>
</form>

<?php if ($dut1): ?>
    <section class="carte">
        <h2>Ajouter une signature pour <?= e($dut1['prenom'] . ' ' . $dut1['nom']) ?></h2>
        <?php if (!$possibles): ?>
            <p class="petit">Tous les signataires actifs ont déjà signé pour ce DUT1.</p>
        <?php else: ?>
            <form class="admin-filtres admin-filtres-2" method="post">
                <?= champ_csrf() ?>
                <input type="hidden" name="dut1" value="<?= e($id_filleul) ?>">
                <input type="hidden" name="action" value="ajouter">
                <div class="champ">
                    <label for="id_parrain">Signataire</label>
                    <select id="id_parrain" name="id_parrain" required>
                        <option value="">Choisir…</option>
                        <?php foreach ($possibles as $p): ?>
                            <option value="<?= e($p['id_parrain']) ?>"><?= e($p['nom'] . ' ' . $p['prenom'] . ' · ' . $p['type_parrain'] . ' · ch. ' . $p['chambre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button class="btn">Ajouter</button>
            </form>
        <?php endif; ?>
    </section>

    <section class="carte">
        <h2>Signatures reçues (<?= e(count($signatures)) ?>)</h2>
        <?php if (!$signatures): ?>
            <p class="petit">Aucune signature pour l’instant.</p>
        <?php else: ?>
            <div class="admin-tableau-defilant">
                <table class="tableau">
                    <thead><tr><th>Signataire</th><th>Type</th><th>Date</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($signatures as $s): ?>
                        <tr>
                            <td><a href="<?= e(admin_url('utilisateur_voir.php', 'parrain', (int) $s['id_parrain'])) ?>"><?= e($s['prenom'] . ' ' . $s['nom']) ?></a></td>
                            <td><?= e($s['type_parrain']) ?></td>
                            <td><?= e(date('d/m/Y H:i', strtotime($s['date_signature']))) ?></td>
                            <td>
                                <form method="post" data-confirmer="Retirer la signature de <?= e($s['prenom'] . ' ' . $s['nom']) ?> ?">
                                    <?= champ_csrf() ?>
                                    <input type="hidden" name="dut1" value="<?= e($id_filleul) ?>">
                                    <input type="hidden" name="id_parrain" value="<?= e($s['id_parrain']) ?>">
                                    <input type="hidden" name="action" value="retirer">
                                    <button class="btn btn-danger admin-btn-petit">Retirer</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
<?php endif; ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>
