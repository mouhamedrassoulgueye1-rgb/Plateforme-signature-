<?php
/**
 * DUT1 – Registre des parrains et Mames : liste complète, case « a signé » cochée automatiquement,
 * filtres pavillon › étage › chambre et « pas encore signé » (formulaire GET, sans JavaScript).
 * registre.php?non_signes=1 → « Pas encore signé » pré-coché.
 */
require_once __DIR__ . '/../includes/init.php';
$moi = exiger_role('dut1');
require_once __DIR__ . '/chambres.php';

$req = $pdo->prepare(
    'SELECT p.prenom, p.nom, p.type_parrain, p.chambre, p.photo, (s.id_parrain IS NOT NULL) AS a_signe
     FROM parrain p
     LEFT JOIN signature s ON s.id_parrain = p.id_parrain AND s.id_filleul = ?
     WHERE p.statut_compte = \'actif\'
     ORDER BY p.nom, p.prenom'
);
$req->execute([$moi['id']]);
$registre = $req->fetchAll();
$arbre    = construire_arbre($registre);        // ajoute aussi $ligne['info']

// Filtres : chaque valeur est vérifiée contre l'arbre (une valeur inconnue est ignorée)
$pavillon   = (string) ($_GET['pavillon'] ?? '');
$etage      = (string) ($_GET['etage'] ?? '');
$chambre    = (string) ($_GET['chambre'] ?? '');
$non_signes = !empty($_GET['non_signes']);
if ($pavillon !== '' && !isset($arbre[$pavillon])) {
    $pavillon = '';
}
if ($etage !== '' && ($pavillon === '' || !isset($arbre[$pavillon][$etage]))) {
    $etage = '';
}
if ($chambre !== '' && ($etage === '' || !ctype_digit($chambre) || !in_array((int) $chambre, $arbre[$pavillon][$etage], true))) {
    $chambre = '';
}

$affiches = array_filter($registre, function (array $r) use ($pavillon, $etage, $chambre, $non_signes): bool {
    $i = $r['info'];
    return ($pavillon === '' || $i['pavillon'] === $pavillon)
        && ($etage === '' || $i['etage'] === $etage)
        && ($chambre === '' || (string) $i['chambre'] === $chambre)
        && (!$non_signes || !$r['a_signe']);
});
$n = count($affiches);

$titre_page = 'Registre';
require __DIR__ . '/../includes/header.php';
?>
<section class="carte registre-carte">
    <h1>Registre des parrains et Mames</h1>
    <p>Retrouvez chaque parrain et Mame par pavillon, étage et chambre.</p>

    <form class="formulaire registre-filtres" method="get">
        <div class="champ">
            <label for="registre-pavillon">Pavillon</label>
            <select id="registre-pavillon" name="pavillon">
                <option value="">Tous les pavillons</option>
                <?php foreach (array_keys($arbre) as $pav): ?>
                    <option value="<?= e($pav) ?>"<?= $pav === $pavillon ? ' selected' : '' ?>><?= $pav === '?' ? 'Chambre non reconnue' : 'Pavillon ' . e($pav) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="champ">
            <label for="registre-etage">Étage</label>
            <select id="registre-etage" name="etage"<?= $pavillon === '' ? ' disabled' : '' ?>>
                <option value="">Tous les étages</option>
                <?php foreach ($pavillon !== '' ? array_keys($arbre[$pavillon]) : [] as $et): ?>
                    <option value="<?= e($et) ?>"<?= $et === $etage ? ' selected' : '' ?>><?= e($et === ETAGE_AUCUN ? 'Sans étage' : $et) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="champ">
            <label for="registre-chambre">Chambre</label>
            <select id="registre-chambre" name="chambre"<?= $etage === '' ? ' disabled' : '' ?>>
                <option value="">Toutes les chambres</option>
                <?php foreach ($etage !== '' ? $arbre[$pavillon][$etage] : [] as $ch): ?>
                    <option value="<?= e($ch) ?>"<?= (string) $ch === $chambre ? ' selected' : '' ?>><?= e($ch) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <label class="registre-case">
            <input type="checkbox" name="non_signes" value="1"<?= $non_signes ? ' checked' : '' ?>> Pas encore signé
        </label>
        <button class="btn">Filtrer</button>
        <a href="<?= e(url('dut1/registre.php')) ?>">Réinitialiser</a>
    </form>
    <p><small>Choisissez un pavillon puis cliquez sur « Filtrer » pour affiner par étage, puis par chambre.</small></p>

    <p class="registre-compteur"><?= e($n) ?> <?= $n > 1 ? 'personnes affichées' : 'personne affichée' ?></p>

    <?php if ($n > 0): ?>
        <ul class="signataire-liste">
            <?php foreach ($affiches as $r): ?>
                <li class="signataire-ligne">
                    <input type="checkbox" disabled<?= $r['a_signe'] ? ' checked' : '' ?> aria-label="<?= $r['a_signe'] ? 'A signé' : 'Pas encore signé' ?>">
                    <?= html_avatar($r) ?>
                    <strong><?= e($r['prenom'] . ' ' . $r['nom']) ?></strong>
                    <small><?= e(libelle_type_parrain($r['type_parrain']) . ' · ' . $r['info']['affiche']) ?></small>
                    — <?= $r['a_signe'] ? 'A signé' : 'Pas encore signé' ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>Aucun parrain ni Mame ne correspond à ces filtres.</p>
    <?php endif; ?>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
