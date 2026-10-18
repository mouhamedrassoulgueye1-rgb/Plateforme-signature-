<?php
/**
 * DUT1 – Liste des personnes qui ont signé pour moi (avec lien WhatsApp).
 * Version de base : la page « Liste des signatures DUT1 » complète (rang parmi les camarades, mise à jour,
 * etc.) est le travail de l'équipe 5, qui peut remplacer ce fichier.
 */
require __DIR__ . '/commun.php';
require __DIR__ . '/chambres.php';

$dut1 = exiger_filleul($pdo);

$st = $pdo->prepare(
    'SELECT p.prenom, p.nom, p.type_parrain, p.chambre, p.telephone, p.photo, s.date_signature
     FROM signature s JOIN parrain p ON p.id_parrain = s.id_parrain
     WHERE s.id_filleul = ? ORDER BY s.date_signature DESC'
);
$st->execute([(int) $dut1['id_filleul']]);
$signataires = $st->fetchAll();

$lignes = '';
foreach ($signataires as $s) {
    $lignes .= vue('ligne_signataire', [
        'html_avatar'   => avatar_html($s),
        'nom'           => $s['prenom'] . ' ' . $s['nom'],
        'detail'        => $s['type_parrain'] . ' · ' . analyser_chambre($s['chambre'])['affiche'] . ' · signé le ' . date_fr($s['date_signature']),
        'lien_whatsapp' => lien_whatsapp($s['telephone']),
    ]);
}
if ($lignes === '') {
    $lignes = vue('liste_vide', ['texte' => 'Personne n\'a encore signé pour toi.']);
}

$n = count($signataires);
$contenu = vue('signatures', [
    'html_entete' => vue('entete', ['titre' => 'Mes signatures', 'sous_titre' => 'Les parrains et Mames qui ont signé pour toi.']),
    'html_lignes' => $lignes,
    'html_pied'   => vue('pied'),
    'titre_liste' => $n . ($n > 1 ? ' signatures reçues' : ' signature reçue'),
]);

echo page('Mes signatures', $contenu, [], 'signatures');
