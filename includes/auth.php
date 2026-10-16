<?php
/**
 * Comptes et sessions. Trois rôles, toujours écrits ainsi : 'dut1', 'parrain', 'admin'.
 *
 * Contenu de la session une fois connecté (ne pas en inventer d'autres) :
 *   $_SESSION['utilisateur'] = ['role' => 'dut1', 'id' => 12, 'nom_utilisateur' => 'awa.ndiaye',
 *                               'prenom' => 'Awa', 'nom' => 'Ndiaye'];
 */

/** Table, clé primaire, page de connexion et page d'accueil de chaque rôle. */
const ROLES = [
    'dut1'    => ['table' => 'filleul',        'id' => 'id_filleul', 'connexion' => 'connexion/dut1.php',    'accueil' => 'dut1/accueil.php'],
    'parrain' => ['table' => 'parrain',        'id' => 'id_parrain', 'connexion' => 'connexion/parrain.php', 'accueil' => 'parrain/accueil.php'],
    'admin'   => ['table' => 'administrateur', 'id' => 'id_admin',   'connexion' => 'admin/connexion.php',   'accueil' => 'admin/index.php'],
];

/**
 * Vérifie un nom d'utilisateur et un mot de passe pour un rôle.
 * Renvoie la ligne de la base (sans le mot de passe) si tout est bon, null sinon.
 * Un compte suspendu par l'admin est refusé.
 * Utilisée par la connexion (Équipe 2), la box de signature (Équipe 3) et l'admin (Équipe 6).
 */
function verifier_identifiants(PDO $pdo, string $role, string $nom_utilisateur, string $mot_de_passe): ?array
{
    $r = ROLES[$role];
    $req = $pdo->prepare('SELECT * FROM ' . $r['table'] . ' WHERE nom_utilisateur = ?');
    $req->execute([normaliser_nom_utilisateur($nom_utilisateur)]);
    $ligne = $req->fetch();
    if (!$ligne || !password_verify($mot_de_passe, $ligne['mot_de_passe'])) {
        return null;
    }
    if (($ligne['statut_compte'] ?? 'actif') !== 'actif') {
        return null;
    }
    unset($ligne['mot_de_passe']);
    $ligne['id'] = (int) $ligne[$r['id']];
    return $ligne;
}

/** Ouvre la session après une connexion réussie. $utilisateur : résultat de verifier_identifiants(). */
function ouvrir_session(string $role, array $utilisateur): void
{
    session_regenerate_id(true);
    $_SESSION['utilisateur'] = [
        'role'            => $role,
        'id'              => (int) $utilisateur['id'],
        'nom_utilisateur' => $utilisateur['nom_utilisateur'],
        'prenom'          => $utilisateur['prenom'],
        'nom'             => $utilisateur['nom'],
    ];
}

/** Utilisateur connecté, ou null. */
function utilisateur(): ?array
{
    return $_SESSION['utilisateur'] ?? null;
}

/**
 * Protège une page : à mettre juste après init.php, par ex. $moi = exiger_role('dut1');
 * Redirige vers la page de connexion du rôle si personne (ou quelqu'un d'autre) est connecté.
 */
function exiger_role(string $role): array
{
    $u = utilisateur();
    if ($u === null || $u['role'] !== $role) {
        flash('erreur', 'Connectez-vous pour accéder à cette page.');
        rediriger(ROLES[$role]['connexion']);
    }
    return $u;
}

/** Déconnexion complète. */
function fermer_session(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 3600, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}
