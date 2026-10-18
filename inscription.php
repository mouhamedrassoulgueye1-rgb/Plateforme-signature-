<?php
/**
 * Traitement de l'inscription (DUT1 et parrains / marraines / Mames).
 *
 * Placé à la RACINE du site, à côté de inscription-dut1.html et inscription-parrain.html.
 * Reçoit le formulaire en POST, enregistre le compte,
 * puis redirige vers la page d'inscription du même profil avec le résultat :
 *   ?succes=1&role=dut1|parrain        -> inscription.js redirige vers l'accueil
 *   ?erreur=<code>&role=dut1|parrain   -> inscription.js affiche le message du code
 *
 * Champs attendus (attribut name du formulaire) :
 *   role                    dut1 | parrain   (champ caché)
 *   nom, prenom, nom_utilisateur, telephone
 *   mot_de_passe, confirmation
 *   photo                   fichier image, facultatif
 *   type_parrain, niveau    uniquement si role = parrain
 */
declare(strict_types=1);

require __DIR__ . '/config/db.php';   // doit définir $pdo (connexion PDO)

/* ---------- Réglages ---------- */
// Page de formulaire à rouvrir après le traitement, selon le profil
const PAGES_RETOUR     = ['dut1' => 'inscription-dut1.html', 'parrain' => 'inscription-parrain.html'];
const PAGE_PAR_DEFAUT  = 'accueil-dut1.html';   // si le profil est inconnu
const DOSSIER_PHOTO = __DIR__ . '/uploads/photos';
const URL_PHOTO     = 'uploads/photos';        // chemin enregistré en base
const PHOTO_MAX     = 2 * 1024 * 1024;         // 2 Mo
const MDP_MIN       = 8;                       // longueur minimale du mot de passe
const MDP_MAX_OCTETS = 72;                     // limite de bcrypt (au-delà, le mot de passe serait tronqué)
const TYPES_PARRAIN = ['Parrain', 'Marraine', 'Mame'];

function retour(array $params): void
{
    $page = PAGES_RETOUR[$params['role'] ?? ''] ?? PAGE_PAR_DEFAUT;
    header('Location: ' . $page . '?' . http_build_query($params), true, 303);
    exit;
}

function texte(string $cle): string
{
    $v = $_POST[$cle] ?? '';
    return is_string($v) ? trim($v) : '';
}

/* ---------- 1) Requête et profil ---------- */
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    retour(['erreur' => 'erreur_serveur']);
}
$role = texte('role');
if (!in_array($role, ['dut1', 'parrain'], true)) {
    retour(['erreur' => 'role_invalide']);
}
$echec = fn (string $code) => retour(['erreur' => $code, 'role' => $role]);

/* ---------- 2) Champs communs ---------- */
$nom      = texte('nom');
$prenom   = texte('prenom');
$login    = texte('nom_utilisateur');
$mdp      = is_string($_POST['mot_de_passe'] ?? null) ? $_POST['mot_de_passe'] : '';
$confirm  = is_string($_POST['confirmation'] ?? null) ? $_POST['confirmation'] : '';

if ($nom === '' || $prenom === '' || $login === '' || $mdp === '' || texte('telephone') === '') {
    $echec('champ_manquant');
}
if (mb_strlen($nom) > 50 || mb_strlen($prenom) > 80
    || !preg_match("/^\p{L}[\p{L}\p{M} '.-]*$/u", $nom)
    || !preg_match("/^\p{L}[\p{L}\p{M} '.-]*$/u", $prenom)) {
    $echec('nom_invalide');
}
if (!preg_match('/^[A-Za-z0-9._-]{3,30}$/', $login)) {
    $echec('nom_utilisateur_invalide');
}

// Téléphone : format international, ex. +221771234567 (sert au lien WhatsApp)
$tel = preg_replace('/[\s().-]+/', '', texte('telephone'));
if (str_starts_with($tel, '00')) {
    $tel = '+' . substr($tel, 2);
}
if (!preg_match('/^\+[1-9][0-9]{7,14}$/', $tel)) {
    $echec('telephone_invalide');
}

if (mb_strlen($mdp) < MDP_MIN || strlen($mdp) > MDP_MAX_OCTETS) {
    $echec('mot_de_passe_invalide');
}
if (!hash_equals($mdp, $confirm)) {
    $echec('mots_de_passe_differents');
}

/* ---------- 3) Champs propres aux parrains (RG2, RG3) ---------- */
$type = null;
$niveau = null;
if ($role === 'parrain') {
    $type = texte('type_parrain');
    if (!in_array($type, TYPES_PARRAIN, true)) {
        $echec('type_invalide');
    }
    $niveau = texte('niveau');
    if ($niveau === '') {
        $echec('niveau_manquant');
    }
    if (mb_strlen($niveau) > 30 || !preg_match("/^[\p{L}\p{N} .'-]+$/u", $niveau)) {
        $echec('niveau_invalide');
    }
}

/* ---------- 4) Nom d'utilisateur unique dans TOUTES les tables ---------- */
try {
    $req = $pdo->prepare(
        'SELECT 1 FROM filleul WHERE nom_utilisateur = ?
         UNION ALL SELECT 1 FROM parrain WHERE nom_utilisateur = ?
         UNION ALL SELECT 1 FROM administrateur WHERE nom_utilisateur = ?
         LIMIT 1'
    );
    $req->execute([$login, $login, $login]);
    if ($req->fetchColumn() !== false) {
        $echec('nom_utilisateur_pris');
    }
} catch (PDOException $e) {
    error_log('[inscription] ' . $e->getMessage());
    $echec('erreur_serveur');
}

/* ---------- 5) Photo facultative ---------- */
$cheminPhoto = null;     // chemin enregistré en base
$fichierPhoto = null;    // fichier sur le disque (pour l'effacer en cas d'échec)
$f = $_FILES['photo'] ?? null;
if (is_array($f) && ($f['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
    if ($f['error'] !== UPLOAD_ERR_OK || $f['size'] > PHOTO_MAX || !is_uploaded_file($f['tmp_name'])) {
        $echec('photo_invalide');
    }
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
    $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    if (!isset($extensions[$mime]) || @getimagesize($f['tmp_name']) === false) {
        $echec('photo_invalide');
    }
    if (!is_dir(DOSSIER_PHOTO) && !mkdir(DOSSIER_PHOTO, 0755, true)) {
        error_log('[inscription] impossible de créer ' . DOSSIER_PHOTO);
        $echec('erreur_serveur');
    }
    // Nom aléatoire : on n'utilise jamais le nom envoyé par l'utilisateur
    $nomFichier = bin2hex(random_bytes(16)) . '.' . $extensions[$mime];
    $fichierPhoto = DOSSIER_PHOTO . '/' . $nomFichier;
    if (!move_uploaded_file($f['tmp_name'], $fichierPhoto)) {
        $echec('erreur_serveur');
    }
    $cheminPhoto = URL_PHOTO . '/' . $nomFichier;
}

/* ---------- 6) Enregistrement ---------- */
$hash = password_hash($mdp, PASSWORD_DEFAULT);   // bcrypt (RG10)

try {
    if ($role === 'dut1') {
        $ins = $pdo->prepare(
            'INSERT INTO filleul (nom, prenom, nom_utilisateur, telephone, photo, mot_de_passe)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $ins->execute([$nom, $prenom, $login, $tel, $cheminPhoto, $hash]);
    } else {
        $ins = $pdo->prepare(
            'INSERT INTO parrain (nom, prenom, nom_utilisateur, telephone, type_parrain, niveau, photo, mot_de_passe)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $ins->execute([$nom, $prenom, $login, $tel, $type, $niveau, $cheminPhoto, $hash]);
    }
} catch (PDOException $e) {
    if ($fichierPhoto !== null && is_file($fichierPhoto)) {
        unlink($fichierPhoto);
    }
    // 1062 = doublon : quelqu'un a pris ce nom d'utilisateur entre-temps
    if ((int) ($e->errorInfo[1] ?? 0) === 1062 || $e->getCode() === '23000') {
        $echec('nom_utilisateur_pris');
    }
    error_log('[inscription] ' . $e->getMessage());
    $echec('erreur_serveur');
}

retour(['succes' => 1, 'role' => $role]);