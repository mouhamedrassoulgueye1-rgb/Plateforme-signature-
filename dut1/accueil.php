<?php
/**
 * DUT1 – Accueil : compteur de signatures reçues, bouton « Se faire signer », code QR,
 * actions rapides, checklist (aperçu).
 * HTML : dut1.html · CSS : dut1.css · JS : dut1.js (même dossier)
 */
require __DIR__ . '/commun.php';
require __DIR__ . '/chambres.php';

$dut1 = exiger_filleul($pdo);
$idFilleul = (int) $dut1['id_filleul'];

// Compteur et total (RG8 : calculés, jamais stockés)
$st = $pdo->prepare('SELECT COUNT(*) FROM signature WHERE id_filleul = ?');
$st->execute([$idFilleul]);
$nbSignatures = (int) $st->fetchColumn();

$totalParrains = (int) $pdo->query("SELECT COUNT(*) FROM parrain WHERE statut_compte = 'actif'")->fetchColumn();
$pourcent = $totalParrains > 0 ? min(100, (int) round($nbSignatures / $totalParrains * 100)) : 0;

// Checklist (aperçu) : 5 lignes, signatures les plus récentes d'abord
$st = $pdo->prepare(
    'SELECT p.prenom, p.nom, p.type_parrain, p.chambre, p.photo, (s.id_parrain IS NOT NULL) AS a_signe
     FROM parrain p
     LEFT JOIN signature s ON s.id_parrain = p.id_parrain AND s.id_filleul = ?
     WHERE p.statut_compte = \'actif\'
     ORDER BY a_signe DESC, s.date_signature DESC, p.nom
     LIMIT 5'
);
$st->execute([$idFilleul]);

$lignes = '';
foreach ($st->fetchAll() as $r) {
    $lignes .= vue('ligne', [
        'classe'      => $r['a_signe'] ? 'fait' : '',
        'signe'       => $r['a_signe'] ? '1' : '0',
        'etat'        => $r['a_signe'] ? 'A signé : ' : 'Pas encore signé : ',
        'html_avatar' => avatar_html($r),
        'nom'         => $r['prenom'] . ' ' . $r['nom'],
        'type'        => $r['type_parrain'],
        'chambre'     => analyser_chambre($r['chambre'])['affiche'],
    ]);
}
if ($lignes === '') {
    $lignes = vue('liste_vide', ['texte' => 'Aucun parrain ni Mame inscrit pour l\'instant.']);
}

$flash = flash_lire();
$contenu = vue('accueil', [
    'html_entete'    => vue('entete', ['titre' => 'Accueil', 'sous_titre' => 'Bienvenue ' . $dut1['prenom'] . ' ! Fais-toi signer par tes parrains et Mames.']),
    'html_flash'     => $flash ? vue('message', ['type' => $flash['type'], 'role' => 'status', 'texte' => $flash['message']]) : '',
    'html_checklist' => $lignes,
    'html_pied'      => vue('pied'),
    'prenom'         => $dut1['prenom'],
    'nb'             => $nbSignatures,
    'total'          => $totalParrains,
    'pourcent'       => $pourcent,
    'url_qr'         => url_site() . '/parrain/valider.php?id_filleul=' . $idFilleul,   // cible du QR : parrain/valider.php
]);

echo page('Accueil', $contenu, [
    'https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js',
    'dut1.js',
], 'accueil');
