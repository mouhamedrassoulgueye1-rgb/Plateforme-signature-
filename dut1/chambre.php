<?php
/**
 * dut1/chambres.php – la colonne parrain.chambre ("29G", "9G", "G29", "29 g"…) :
 * pavillons, plages d'étages et analyse. Utilisé par dut1/accueil.php, dut1/registre.php,
 * dut1/telecharger.php et dut1/signatures.php.
 */

// =============================================================================
// CHAMBRES : pavillons, étages, analyse de la colonne « chambre »
// =============================================================================
// Pour modifier les étages : changer les plages (inclusives, sans chevauchement,
// nom d'étage non numérique, virgule après CHAQUE ligne).
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

const ETAGE_AUCUN   = '_';                 // pavillon sans étages configurés
const ETAGE_INCONNU = 'Étage non défini';  // chambre hors de toutes les plages

/** Transforme la colonne « chambre » ("29G", "G29", "29 g"…) en pavillon / chambre / étage. */
function analyser_chambre(?string $texte): array
{
    $brut = trim((string) $texte);
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
// AIDES D'AFFICHAGE de l'espace DUT1 (utilisées par accueil, registre, signatures et telecharger)
// =============================================================================

/** Date lisible : 20/10/2026 14:32 (même format que l'administration). */
if (!function_exists('formater_date')) {
    function formater_date(string $date): string
    {
        return (new DateTime($date))->format('d/m/Y H:i');
    }
}

/** « Mame1 » → « Mame 1 » (les autres types restent tels quels). */
if (!function_exists('libelle_type_parrain')) {
    function libelle_type_parrain(string $type): string
    {
        return preg_replace('/^(Mame)(\d)$/', '$1 $2', $type);
    }
}

/** Photo de la personne (colonne « photo ») ou, à défaut, ses initiales. */
if (!function_exists('html_avatar')) {
    function html_avatar(array $personne, int $taille = 44): string
    {
        if (!empty($personne['photo'])) {
            return '<img class="avatar-signataire" width="' . $taille . '" height="' . $taille . '" src="' . e(url($personne['photo'])) . '" alt="">';
        }
        $initiales = mb_strtoupper(mb_substr((string) $personne['prenom'], 0, 1) . mb_substr((string) $personne['nom'], 0, 1));
        return '<strong class="avatar-signataire avatar-signataire-initiales" aria-hidden="true">' . e($initiales) . '</strong>';
    }
}

/** Neutralise les cellules CSV qui commencent par = + - @ (injection de formule Excel). */
if (!function_exists('securiser_cellule_csv')) {
    function securiser_cellule_csv(string $valeur): string
    {
        return preg_match('/^[=+\-@\t\r]/', $valeur) ? "'" . $valeur : $valeur;
    }
}

/** Téléphone (chiffres seuls en base) écrit pour qu'Excel l'affiche en entier, pas en 2,21771E+11. */
if (!function_exists('formater_telephone_csv')) {
    function formater_telephone_csv(?string $telephone): string
    {
        $chiffres = preg_replace('/\D+/', '', (string) $telephone);
        return $chiffres === '' ? '' : '="' . $chiffres . '"';
    }
}
