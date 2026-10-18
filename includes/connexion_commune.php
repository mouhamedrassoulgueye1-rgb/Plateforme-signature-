<?php
/**
 * Fonctions communes aux trois scripts de connexion
 * (connexion/login-dut1.php, login-parrain.php, login-admin.php).
 *
 * Chaque script de connexion ne vérifie QUE sa propre table : un compte DUT1
 * ne peut pas ouvrir une session parrain ou admin, même si la page est modifiée
 * dans le navigateur. Le rôle envoyé par la page est volontairement ignoré.
 */
declare(strict_types=1);

const MAX_ECHECS     = 5;    // tentatives ratées autorisées...
const DUREE_BLOCAGE  = 60;   // ...avant un blocage de 60 secondes (par navigateur)

function demarrer_session_securisee(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    session_set_cookie_params([
        'lifetime' => 0,          // le cookie disparaît à la fermeture du navigateur
        'path'     => '/',
        'secure'   => $https,     // true automatiquement en HTTPS
        'httponly' => true,       // inaccessible au JavaScript
        'samesite' => 'Lax',
    ]);
    session_start();
}

function reponse_json(array $donnees, int $statut = 200): void
{
    http_response_code($statut);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($donnees, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Vérifie l'identifiant et le mot de passe dans la table du profil, puis ouvre la session.
 *
 * $cfg = [
 *   'role'     => 'dut1' | 'parrain' | 'admin',   // rôle écrit dans la session
 *   'table'    => 'filleul' | 'parrain' | 'administrateur',
 *   'id'       => 'id_filleul' | 'id_parrain' | 'id_admin',
 *   'statut'   => true si la table a la colonne statut_compte (RG9),
 *   'redirect' => page ouverte après la connexion,
 * ]
 * Les valeurs de $cfg sont écrites dans nos scripts : elles ne viennent jamais de l'utilisateur.
 */
function connecter(PDO $pdo, array $cfg): void
{
    // 1) Méthode et format de la requête (le JSON bloque aussi les formulaires venant d'un autre site)
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        header('Allow: POST');
        reponse_json(['ok' => false, 'error' => 'Méthode non autorisée.'], 405);
    }
    $type = $_SERVER['CONTENT_TYPE'] ?? '';
    if (stripos($type, 'application/json') !== 0) {
        reponse_json(['ok' => false, 'error' => 'Requête invalide.'], 415);
    }
    $corps = json_decode((string) file_get_contents('php://input'), true);
    $identifiant = is_array($corps) && is_string($corps['username'] ?? null) ? trim($corps['username']) : '';
    $motDePasse  = is_array($corps) && is_string($corps['password'] ?? null) ? $corps['password'] : '';

    if ($identifiant === '' || $motDePasse === '') {
        reponse_json(['ok' => false, 'error' => 'Saisis ton nom d\'utilisateur et ton mot de passe.'], 400);
    }
    if (mb_strlen($identifiant) > 30 || strlen($motDePasse) > 200) {
        reponse_json(['ok' => false, 'error' => 'Nom d\'utilisateur ou mot de passe incorrect.'], 401);
    }

    demarrer_session_securisee();

    // 2) Limite de tentatives (stockée dans la session du navigateur)
    $e = $_SESSION['echecs_connexion'] ?? ['n' => 0, 'debut' => time()];
    if (time() - $e['debut'] >= DUREE_BLOCAGE) {
        $e = ['n' => 0, 'debut' => time()];
    }
    if ($e['n'] >= MAX_ECHECS) {
        $reste = DUREE_BLOCAGE - (time() - $e['debut']);
        reponse_json(['ok' => false, 'error' => "Trop de tentatives. Réessaie dans {$reste} secondes."], 429);
    }

    // 3) Recherche du compte (requête préparée)
    $colStatut = $cfg['statut'] ? ', statut_compte' : '';
    $sql = "SELECT {$cfg['id']} AS id, nom, prenom, nom_utilisateur, mot_de_passe{$colStatut}
            FROM {$cfg['table']} WHERE nom_utilisateur = ? LIMIT 1";
    try {
        $req = $pdo->prepare($sql);
        $req->execute([$identifiant]);
        $compte = $req->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $ex) {
        error_log('[connexion ' . $cfg['role'] . '] ' . $ex->getMessage());
        reponse_json(['ok' => false, 'error' => 'Erreur du serveur. Réessaie plus tard.'], 500);
    }

    // 4) Mot de passe (message identique que le compte existe ou non)
    if (!$compte || !password_verify($motDePasse, $compte['mot_de_passe'])) {
        $e['n']++;
        $_SESSION['echecs_connexion'] = $e;
        reponse_json(['ok' => false, 'error' => 'Nom d\'utilisateur ou mot de passe incorrect.'], 401);
    }

    // 5) Compte suspendu par l'administrateur (RG9)
    if ($cfg['statut'] && ($compte['statut_compte'] ?? 'actif') !== 'actif') {
        reponse_json(['ok' => false, 'error' => 'Ce compte est suspendu. Contacte l\'administration.'], 403);
    }

    // 6) Mise à jour du hash si l'algorithme par défaut de PHP a évolué
    if (password_needs_rehash($compte['mot_de_passe'], PASSWORD_DEFAULT)) {
        try {
            $maj = $pdo->prepare("UPDATE {$cfg['table']} SET mot_de_passe = ? WHERE {$cfg['id']} = ?");
            $maj->execute([password_hash($motDePasse, PASSWORD_DEFAULT), $compte['id']]);
        } catch (PDOException $ex) {
            error_log('[connexion ' . $cfg['role'] . '] rehash : ' . $ex->getMessage());
        }
    }

    // 7) Ouverture de la session : nouvel identifiant de session, ancien contenu effacé
    session_regenerate_id(true);
    $_SESSION = [
        'utilisateur_id'  => (int) $compte['id'],
        'role'            => $cfg['role'],
        'nom_utilisateur' => $compte['nom_utilisateur'],
        'prenom'          => $compte['prenom'],
        'nom'             => $compte['nom'],
        'connecte_a'      => time(),
    ];

    reponse_json(['ok' => true, 'redirect' => $cfg['redirect']]);
}