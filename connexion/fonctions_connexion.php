<?php
/**
 * Équipes 1-2 : traitement commun aux pages connexion/dut1.php et connexion/parrain.php.
 *
 * Chaque page ne vérifie QUE la table de son rôle : un compte DUT1 ne peut pas ouvrir une session
 * parrain, même si la page est modifiée dans le navigateur. Le mot de passe et la session passent
 * par les fonctions communes de includes/auth.php (verifier_identifiants(), ouvrir_session()) :
 * le format de $_SESSION['utilisateur'] est donc le même que pour exiger_role() et l'admin.
 */

const CONNEXION_ESSAIS_MAX = 5;    // tentatives ratées autorisées...
const CONNEXION_BLOCAGE    = 60;   // ...avant un blocage de 60 secondes (compteur dans la session du navigateur)

/**
 * Traite le formulaire de connexion d'un rôle ('dut1' ou 'parrain').
 * Si tout est bon : ouvre la session et redirige vers l'accueil du rôle (ROLES).
 * Sinon : renvoie le message d'erreur à afficher sous le formulaire.
 */
function traiter_connexion(PDO $pdo, string $role): string
{
    verifier_csrf();
    $nom_utilisateur = normaliser_nom_utilisateur((string) ($_POST['nom_utilisateur'] ?? ''));
    $mot_de_passe = (string) ($_POST['mot_de_passe'] ?? '');

    if ($nom_utilisateur === '' || $mot_de_passe === '') {
        return 'Saisis ton nom d’utilisateur et ton mot de passe.';
    }

    // Limite de tentatives
    $echecs = $_SESSION['echecs_connexion'] ?? ['n' => 0, 'debut' => time()];
    if (time() - $echecs['debut'] >= CONNEXION_BLOCAGE) {
        $echecs = ['n' => 0, 'debut' => time()];
    }
    if ($echecs['n'] >= CONNEXION_ESSAIS_MAX) {
        $reste = CONNEXION_BLOCAGE - (time() - $echecs['debut']);
        return 'Trop de tentatives. Réessaie dans ' . $reste . ' secondes.';
    }

    // Message identique que le compte existe ou non (et pour un compte suspendu)
    $compte = verifier_identifiants($pdo, $role, $nom_utilisateur, $mot_de_passe);
    if ($compte === null) {
        $echecs['n']++;
        $_SESSION['echecs_connexion'] = $echecs;
        return 'Nom d’utilisateur ou mot de passe incorrect.';
    }

    unset($_SESSION['echecs_connexion']);
    ouvrir_session($role, $compte);
    rediriger(ROLES[$role]['accueil']);
    return '';
}
