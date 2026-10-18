<?php
/**
 * PARRAIN / MAME – Accueil : nombre de signatures effectuées, bouton « Scanner un QR code »,
 * actions rapides, dernières signatures, mode d'emploi.
 * HTML : parrain.html · CSS : parrain.css · JS : parrain.js (même dossier)
 */
require __DIR__ . '/commun.php';

$p = exiger_parrain($pdo);

$st = $pdo->prepare('SELECT COUNT(*) FROM signature WHERE id_parrain = ?');
$st->execute([(int) $p['id_parrain']]);
$nb = (int) $st->fetchColumn();                      // calculé à chaque affichage, jamais stocké

$totalDut1 = (int) $pdo->query("SELECT COUNT(*) FROM filleul WHERE statut_compte = 'actif'")->fetchColumn();

$flash = flash_lire();
$contenu = vue('accueil', [
    'html_entete'    => vue('entete', ['titre' => 'Accueil', 'sous_titre' => 'Bienvenue ' . $p['prenom'] . ' ! Scanne le QR code d\'un DUT1 pour signer.']),
    'html_flash'     => $flash ? vue('message', ['type' => $flash['type'], 'role' => 'status', 'texte' => $flash['message']]) : '',
    'html_dernieres' => lignes_dut1(requete_signatures($pdo, (int) $p['id_parrain'], 5)),
    'html_pied'      => vue('pied'),
    'nb'             => $nb,
    'total_dut1'     => $totalDut1,
    'phrase'         => $nb === 0 ? 'Tu n\'as pas encore signé pour un DUT1.' : 'Merci pour ton engagement !',
]);

echo page('Accueil', $contenu, [], 'accueil');
