<?php
/**
 * Libellés affichés des types de parrain (Parrain, Marraine, Mame 1…).
 * La valeur enregistrée dans parrain.type_parrain ne change jamais : seul le texte affiché partout sur le site change.
 */
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/fonctions_admin.php';
$moi = exiger_role('admin');

$erreurs = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifier_csrf();
    $saisis = [];
    foreach (TYPES_PARRAIN as $type) {
        $libelle = trim((string) ($_POST['libelle'][$type] ?? ''));
        if ($libelle === '' || mb_strlen($libelle) > 40) {
            $erreurs[$type] = 'Obligatoire, 40 caractères au plus.';
        }
        $saisis[$type] = $libelle;
    }
    if (!$erreurs) {
        $req = $pdo->prepare('INSERT INTO libelle_type (type_parrain, libelle) VALUES (?, ?) ON DUPLICATE KEY UPDATE libelle = VALUES(libelle)');
        foreach ($saisis as $type => $libelle) {
            $req->execute([$type, $libelle]);
        }
        flash('succes', 'Libellés enregistrés : ils s’affichent maintenant partout sur le site.');
        rediriger('admin/libelles.php');
    }
}

// Nombre de comptes par type, pour aider à choisir
$nb_par_type = $pdo->query('SELECT type_parrain, COUNT(*) FROM parrain GROUP BY type_parrain')->fetchAll(PDO::FETCH_KEY_PAIR);
$libelles = libelles_types();

admin_debut('Libellés des types', 'libelles');
?>
<div class="admin-titre">
    <h1>Libellés des types</h1>
</div>
<section class="carte">
    <p class="petit">Le texte affiché pour chaque type de signataire (inscription, registre, listes, exports).
        La valeur enregistrée dans la base ne change pas : tu peux renommer sans risque.</p>
    <?php if ($erreurs): ?>
        <div class="alerte alerte-erreur">Corrige les libellés signalés.</div>
    <?php endif; ?>
    <form method="post">
        <?= champ_csrf() ?>
        <div class="admin-tableau-defilant">
            <table class="tableau">
                <thead><tr><th>Valeur en base</th><th>Libellé affiché</th><th>Comptes</th></tr></thead>
                <tbody>
                <?php foreach (TYPES_PARRAIN as $type): ?>
                    <tr>
                        <td><code><?= e($type) ?></code></td>
                        <td class="champ">
                            <input name="libelle[<?= e($type) ?>]" aria-label="Libellé de <?= e($type) ?>" required maxlength="40"
                                   value="<?= e($_POST['libelle'][$type] ?? $libelles[$type]) ?>">
                            <?php if (isset($erreurs[$type])): ?><span class="admin-erreur-champ"><?= e($erreurs[$type]) ?></span><?php endif; ?>
                        </td>
                        <td><?= e($nb_par_type[$type] ?? 0) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="groupe-boutons"><button class="btn">Enregistrer</button></div>
    </form>
</section>
<?php admin_fin(); ?>
