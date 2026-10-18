<?php
/**
 * PARRAIN / MAME – CIBLE DU QR CODE d'un DUT1 (adresse encodée par dut1/accueil.php) :
 *   valider.php?id_filleul=12
 * GET  → carte du DUT1 + bouton « Confirmer ma signature » (évite une signature par simple lien)
 * POST → enregistre la signature (clé primaire composée : 2e tentative = erreur 1062, RG6)
 * Non connecté : exiger_parrain() mémorise l'adresse et envoie se connecter (équipe 2), puis on revient ici.
 */
require __DIR__ . '/commun.php';

$p = exiger_parrain($pdo);

$idFilleul = (int) ($_GET['id_filleul'] ?? 0);
$st = $pdo->prepare('SELECT id_filleul, prenom, nom, photo FROM filleul WHERE id_filleul = ? AND statut_compte = \'actif\'');
$st->execute([$idFilleul]);
$dut1 = $st->fetch();
if (!$dut1) {
    page_erreur('QR code invalide', 'Ce code ne correspond à aucun DUT1 actif.', 404);
}
$nomDut1 = $dut1['prenom'] . ' ' . $dut1['nom'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valide($_POST['csrf'] ?? null)) {
        flash('echec', 'La page a expiré. Scanne de nouveau le QR code.');
    } else {
        try {
            $ins = $pdo->prepare('INSERT INTO signature (id_parrain, id_filleul) VALUES (?, ?)');
            $ins->execute([(int) $p['id_parrain'], $idFilleul]);
            flash('succes', 'Signature enregistrée pour ' . $nomDut1 . '. Merci !');
        } catch (PDOException $ex) {
            if ((int) ($ex->errorInfo[1] ?? 0) === 1062) {
                flash('echec', 'Signature refusée : tu as déjà signé pour ' . $nomDut1 . '.');
            } else {
                flash('echec', 'La signature a échoué. Réessaie dans un instant.');
            }
        }
    }
    header('Location: accueil.php');
    exit;
}

$st = $pdo->prepare('SELECT COUNT(*) FROM signature WHERE id_parrain = ? AND id_filleul = ?');
$st->execute([(int) $p['id_parrain'], $idFilleul]);
$dejaSigne = (int) $st->fetchColumn() > 0;

$action = $dejaSigne
    ? vue('message', ['type' => 'echec', 'role' => 'status', 'texte' => 'Tu as déjà signé pour cette personne.']) . vue('lien_retour')
    : vue('formulaire_confirmation', ['id' => $idFilleul, 'csrf' => csrf_token()]);

$contenu = vue('confirmer', [
    'html_entete' => vue('entete', ['titre' => 'Confirmer la signature', 'sous_titre' => 'Vérifie la personne avant de confirmer.']),
    'html_avatar' => avatar_html($dut1, 'grand'),
    'html_action' => $action,
    'html_pied'   => vue('pied'),
    'nom_dut1'    => $nomDut1,
]);

echo page('Confirmer la signature', $contenu, [], 'scanner');
