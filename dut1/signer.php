<?php
/**
 * DUT1 – « Box » de signature. Le DUT1 tend son téléphone ; le parrain / la Mame saisit SES identifiants.
 *   succès            → retour à l'accueil avec un message de confirmation
 *   déjà signé (1062) → retour à l'accueil avec un message d'échec (RG6)
 *   identifiants faux → on reste dans la box avec l'erreur, pour réessayer
 */
require __DIR__ . '/commun.php';

$dut1 = exiger_filleul($pdo);
$idFilleul = (int) $dut1['id_filleul'];

$erreur = null;
$saisie = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $saisie = trim((string) ($_POST['nom_utilisateur'] ?? ''));
    $mdp    = (string) ($_POST['mot_de_passe'] ?? '');

    if (!csrf_valide($_POST['csrf'] ?? null)) {
        $erreur = 'La page a expiré. Recharge-la puis réessaie.';
    } elseif ($saisie === '' || $mdp === '') {
        $erreur = 'Renseigne ton nom d\'utilisateur et ton mot de passe.';
    } else {
        $st = $pdo->prepare('SELECT id_parrain, mot_de_passe, statut_compte FROM parrain WHERE nom_utilisateur = ?');
        $st->execute([$saisie]);
        $parrain = $st->fetch();

        if (!$parrain || !password_verify($mdp, $parrain['mot_de_passe'])) {
            $erreur = 'Nom d\'utilisateur ou mot de passe incorrect.';
        } elseif ($parrain['statut_compte'] !== 'actif') {
            $erreur = 'Ce compte est suspendu. Contacte l\'administrateur.';
        } else {
            try {
                $ins = $pdo->prepare('INSERT INTO signature (id_parrain, id_filleul) VALUES (?, ?)');
                $ins->execute([(int) $parrain['id_parrain'], $idFilleul]);
                flash('succes', 'Signature enregistrée. Merci !');
            } catch (PDOException $ex) {
                if ((int) ($ex->errorInfo[1] ?? 0) === 1062) {
                    flash('echec', 'Signature refusée : tu as déjà signé pour cette personne.');
                } else {
                    flash('echec', 'La signature a échoué. Réessaie dans un instant.');
                }
            }
            header('Location: accueil.php');
            exit;
        }
    }
}

$contenu = vue('signer', [
    'html_erreur' => $erreur ? vue('message', ['type' => 'echec', 'role' => 'alert', 'texte' => $erreur]) : '',
    'nom_dut1'    => $dut1['prenom'] . ' ' . $dut1['nom'],
    'csrf'        => csrf_token(),
    'saisie'      => $saisie,
]);

echo page('Signer', $contenu, [], '', 'page-box');
