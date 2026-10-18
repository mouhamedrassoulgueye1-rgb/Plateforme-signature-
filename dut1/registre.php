<?php
/**
 * DUT1 – Registre des parrains et Mames : liste complète, checklist cochée automatiquement,
 * filtrage pavillon › étage › chambre (dut1.js). registre.php?non_signes=1 → « Pas encore signé » pré-coché.
 */
require __DIR__ . '/commun.php';
require __DIR__ . '/chambres.php';

$dut1 = exiger_filleul($pdo);

$st = $pdo->prepare(
    'SELECT p.prenom, p.nom, p.type_parrain, p.chambre, p.photo, (s.id_parrain IS NOT NULL) AS a_signe
     FROM parrain p
     LEFT JOIN signature s ON s.id_parrain = p.id_parrain AND s.id_filleul = ?
     WHERE p.statut_compte = \'actif\'
     ORDER BY p.nom, p.prenom'
);
$st->execute([(int) $dut1['id_filleul']]);
$registre = $st->fetchAll();
$arbre = construire_arbre($registre);          // ajoute aussi $ligne['info']

$lignes = '';
foreach ($registre as $r) {
    $i = $r['info'];
    $lignes .= vue('ligne', [
        'classe'      => $r['a_signe'] ? 'fait' : '',
        'pav'         => $i['pavillon'],
        'etage'       => $i['etage'],
        'chambre_num' => $i['chambre'] ?? '',
        'signe'       => $r['a_signe'] ? '1' : '0',
        'etat'        => $r['a_signe'] ? 'A signé : ' : 'Pas encore signé : ',
        'html_avatar' => avatar_html($r),
        'nom'         => $r['prenom'] . ' ' . $r['nom'],
        'type'        => $r['type_parrain'],
        'chambre'     => $i['affiche'],
    ]);
}

$options = '';
foreach (array_keys($arbre) as $pav) {
    $options .= vue('option', [
        'valeur'  => $pav,
        'libelle' => $pav === '?' ? 'Chambre non reconnue' : 'Pavillon ' . $pav,
    ]);
}

$contenu = vue('registre', [
    'html_entete'      => vue('entete', ['titre' => 'Registre des parrains et Mames', 'sous_titre' => 'Retrouve chaque parrain et Mame par pavillon, étage et chambre.']),
    'html_options_pav' => $options,
    'html_lignes'      => $lignes,
    'html_arbre_json'  => json_encode($arbre, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP),
    'html_pied'        => vue('pied'),
    'aucun'            => ETAGE_AUCUN,
    'non_signes'       => !empty($_GET['non_signes']) ? 'checked' : '',
]);

echo page('Registre', $contenu, ['dut1.js'], 'registre');
