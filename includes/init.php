<?php
/**
 * Démarrage commun à TOUTES les pages. Toujours la première ligne d'une page :
 *   require_once __DIR__ . '/../includes/init.php';   (page dans un sous-dossier)
 *   require_once __DIR__ . '/includes/init.php';      (index.php à la racine)
 *
 * Après cette ligne, vous disposez de :
 *   $pdo                          connexion à la base (PDO, requêtes préparées)
 *   includes/fonctions.php        e(), url(), rediriger(), flash(), csrf, téléphone, photo…
 *   includes/auth.php             verifier_identifiants(), ouvrir_session(), exiger_role()…
 *   includes/signatures.php       enregistrer_signature(), nb_signatures_filleul()…
 */

if (!is_file(__DIR__ . '/../config/db.php')) {
    http_response_code(500);
    exit('Configuration absente : copiez config/db.example.php en config/db.php.');
}
require_once __DIR__ . '/../config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    ]);
    session_start();
}

$pdo = new PDO(
    'mysql:host=' . DB_HOTE . ';dbname=' . DB_NOM . ';charset=utf8mb4',
    DB_UTILISATEUR,
    DB_MOT_DE_PASSE,
    [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]
);

require_once __DIR__ . '/fonctions.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/signatures.php';
