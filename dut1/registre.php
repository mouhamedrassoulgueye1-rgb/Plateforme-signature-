<?php
require_once __DIR__ . '/../includes/init.php';
$moi = exiger_role('dut1');

/** Équipe 3 : registre des signataires avec leur chambre, checklist automatique (« a signé ») et filtres. */

// =============================================================================
// 1. CHAMBRES
// =============================================================================
const PAVILLONS = ['A', 'B', 'C', 'G'];

const ETAGES = [
    'A' => [
        'Rez-de-chaussée' => [1, 5],
        'Étage 1'         => [6, 28],
        'Étage 2'         => [29, 51],
        'Étage 3'         => [52, 75],
    ],
    'B' => [
        'Rez-de-chaussée' => [1, 6],
        'Étage 1'         => [7, 29],
        'Étage 2'         => [30, 52],
        'Étage 3'         => [53, 75],
    ],
    'C' => [
        'Rez-de-chaussée' => [1, 5],
        'Étage 1'         => [6, 28],
        'Étage 2'         => [29, 51],
        'Étage 3'         => [52, 75],
    ],
    'G' => [
        'Étage 1' => [13, 31],
        'Étage 2' => [32, 50],
        'Étage 3' => [51, 69],
        'Étage 4' => [70, 88],
    ],
];

const ETAGE_AUCUN   = '_';
const ETAGE_INCONNU = 'Étage non défini';

/** Transforme la colonne « chambre » ("29G", "G29", "29 g"…) en pavillon / chambre / étage. */
function analyser_chambre(?string $chambre_brute): array
{
    $brut = trim((string) $chambre_brute);
    $pavillon = null;
    $chambre  = null;

    if (preg_match('/^(\d{1,3})[\s-]*([A-Za-z])$/u', $brut, $m)) {          // 29G
        $chambre  = (int) $m[1];
        $pavillon = strtoupper($m[2]);
    } elseif (preg_match('/^([A-Za-z])[\s-]*(\d{1,3})$/u', $brut, $m)) {    // G29
        $chambre  = (int) $m[2];
        $pavillon = strtoupper($m[1]);
    }

    if ($pavillon === null || !in_array($pavillon, PAVILLONS, true)) {
        return ['valide' => false, 'pavillon' => '?', 'chambre' => null,
                'etage' => ETAGE_AUCUN, 'affiche' => $brut !== '' ? $brut : 'Non renseigné'];
    }

    $etage = ETAGE_AUCUN;
    if (!empty(ETAGES[$pavillon])) {
        $etage = ETAGE_INCONNU;
        foreach (ETAGES[$pavillon] as $libelle => [$min, $max]) {
            if ($chambre >= $min && $chambre <= $max) {
                $etage = $libelle;
                break;
            }
        }
    }

    return ['valide' => true, 'pavillon' => $pavillon, 'chambre' => $chambre,
            'etage' => $etage, 'affiche' => $chambre . $pavillon];
}

/** Ajoute 'info' à chaque ligne du registre et retourne l'arbre trié pavillon › étage › [chambres]. */
function construire_arbre(array &$registre): array
{
    $arbre = [];
    foreach ($registre as &$ligne) {
        $ligne['info'] = analyser_chambre($ligne['chambre']);
        $i = $ligne['info'];
        $arbre[$i['pavillon']][$i['etage']][] = $i['chambre'];
    }
    unset($ligne);

    $trie = [];
    foreach (array_merge(PAVILLONS, ['?']) as $pav) {
        if (!isset($arbre[$pav])) {
            continue;
        }
        $ordre = array_merge(array_keys(ETAGES[$pav] ?? []), [ETAGE_INCONNU, ETAGE_AUCUN]);
        foreach ($ordre as $etage) {
            if (!isset($arbre[$pav][$etage])) {
                continue;
            }
            $chambres = array_values(array_unique(array_filter($arbre[$pav][$etage], fn($c) => $c !== null)));
            sort($chambres, SORT_NUMERIC);
            $trie[$pav][$etage] = $chambres;
        }
    }
    return $trie;
}

// =============================================================================
// 2. DONNÉES
// =============================================================================
// La version précédente rendait le HTML avec des gabarits lus dans dut1/equipe3.html et un script
// equipe3.js, qui n'ont jamais été envoyés sur GitHub : la page restait vide. En attendant leur
// version finale, l'Équipe 3 dispose ici d'un affichage simple au format commun (header.php / footer.php),
// avec les filtres en GET (pavillon, « pas encore signé »).

$req = $pdo->prepare(
    'SELECT p.prenom, p.nom, p.type_parrain, p.chambre, p.photo, (s.id_parrain IS NOT NULL) AS a_signe
     FROM parrain p
     LEFT JOIN signature s ON s.id_parrain = p.id_parrain AND s.id_filleul = ?
     WHERE p.statut_compte = \'actif\'
     ORDER BY p.nom, p.prenom'
);
$req->execute([$moi['id']]);
$registre = $req->fetchAll();
$arbre = construire_arbre($registre);

$pavillon = (string) ($_GET['pavillon'] ?? '');
if (!isset($arbre[$pavillon])) {
    $pavillon = '';
}
$non_signes = !empty($_GET['non_signes']);

$nb_signes = count(array_filter($registre, fn($r) => (bool) $r['a_signe']));
$lignes = array_filter($registre, fn($r) => ($pavillon === '' || $r['info']['pavillon'] === $pavillon)
    && (!$non_signes || !$r['a_signe']));

// =============================================================================
// 3. PAGE
// =============================================================================
$titre_page = 'Registre des parrains et Mames';
require __DIR__ . '/../includes/header.php';
?>
<section class="carte">
    <h1><?= e($titre_page) ?></h1>
    <p class="petit"><?= e($nb_signes) ?> signature(s) sur <?= e(count($registre)) ?> signataires inscrits.</p>

    <form class="registre-filtres" method="get">
        <div class="champ">
            <label for="pavillon">Pavillon</label>
            <select id="pavillon" name="pavillon">
                <option value="">Tous les pavillons</option>
                <?php foreach (array_keys($arbre) as $pav): ?>
                    <option value="<?= e($pav) ?>" <?= $pav === $pavillon ? 'selected' : '' ?>>
                        <?= e($pav === '?' ? 'Chambre non reconnue' : 'Pavillon ' . $pav) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <label class="registre-case"><input type="checkbox" name="non_signes" value="1" <?= $non_signes ? 'checked' : '' ?>> Pas encore signé seulement</label>
        <button class="btn">Filtrer</button>
    </form>
</section>

<?php if (!$lignes): ?>
    <p class="carte petit">Aucun signataire ne correspond.</p>
<?php else: ?>
    <div class="carte registre-defilant">
        <table class="tableau">
            <thead><tr><th></th><th>Nom</th><th>Type</th><th>Chambre</th><th>Étage</th><th>A signé</th></tr></thead>
            <tbody>
            <?php foreach ($lignes as $r): $i = $r['info']; ?>
                <tr class="<?= $r['a_signe'] ? 'registre-fait' : '' ?>">
                    <td>
                        <?php if (!empty($r['photo'])): ?>
                            <img class="avatar" src="<?= e(url($r['photo'])) ?>" alt="">
                        <?php else: ?>
                            <span class="avatar registre-initiales"><?= e(mb_strtoupper(mb_substr($r['prenom'], 0, 1) . mb_substr($r['nom'], 0, 1))) ?></span>
                        <?php endif; ?>
                    </td>
                    <td><?= e($r['prenom'] . ' ' . $r['nom']) ?></td>
                    <td><?= e($r['type_parrain']) ?></td>
                    <td><?= e($i['affiche']) ?></td>
                    <td><?= e($i['etage'] === ETAGE_AUCUN ? '' : $i['etage']) ?></td>
                    <td><?= $r['a_signe'] ? '✔ Oui' : 'Pas encore' ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>
