<?php
/**
 * PARRAIN / MAME – Les DUT1 pour lesquels j'ai signé.
 *   signatures.php         → la liste (chaque ligne mène à la fiche)
 *   signatures.php?id=12   → fiche d'un DUT1 (réservée à ceux pour qui CE signataire a signé)
 */
require __DIR__ . '/commun.php';

$p = exiger_parrain($pdo);
$idParrain = (int) $p['id_parrain'];

// --- Fiche d'un DUT1 ------------------------------------------------------------
if (isset($_GET['id'])) {
    $st = $pdo->prepare(
        'SELECT f.id_filleul, f.prenom, f.nom, f.telephone, f.photo, s.date_signature,
                (SELECT COUNT(*) FROM signature WHERE id_filleul = f.id_filleul) AS nb_recues
         FROM signature s JOIN filleul f ON f.id_filleul = s.id_filleul
         WHERE s.id_parrain = ? AND f.id_filleul = ?'
    );
    $st->execute([$idParrain, (int) $_GET['id']]);
    $f = $st->fetch();
    if (!$f) {
        page_erreur('Fiche introuvable', 'Tu n\'as pas signé pour cette personne, ou elle n\'existe pas.', 404);
    }

    $contenu = vue('fiche', [
        'html_entete'   => vue('entete', ['titre' => 'Fiche du DUT1']),
        'html_avatar'   => avatar_html($f, 'grand'),
        'html_pied'     => vue('pied'),
        'nom_complet'   => $f['prenom'] . ' ' . $f['nom'],
        'prenom'        => $f['prenom'],
        'nom'           => $f['nom'],
        'telephone'     => $f['telephone'],
        'lien_whatsapp' => lien_whatsapp($f['telephone']),
        'date'          => date_fr($f['date_signature']),
        'nb_recues'     => $f['nb_recues'],
    ]);
    echo page('Fiche du DUT1', $contenu, [], 'signatures');
    exit;
}

// --- Liste ------------------------------------------------------------------------
$lignes = requete_signatures($pdo, $idParrain);
$n = count($lignes);
$contenu = vue('liste', [
    'html_entete' => vue('entete', ['titre' => 'Mes signatures', 'sous_titre' => 'Les DUT1 pour lesquels tu as signé.']),
    'html_lignes' => lignes_dut1($lignes),
    'html_pied'   => vue('pied'),
    'titre_liste' => $n . ($n > 1 ? ' DUT1 signés' : ' DUT1 signé'),
]);
echo page('Mes signatures', $contenu, [], 'signatures');
