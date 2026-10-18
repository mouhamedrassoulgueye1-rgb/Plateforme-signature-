<?php
/**
 * Fonctions communes. Ne pas les dupliquer dans les pages : si une fonction manque,
 * proposez-la ici par une pull request (fichier Commun).
 */

/** Valeurs autorisées pour parrain.type_parrain (identiques à l'ENUM de la base). */
const TYPES_PARRAIN = ['Parrain', 'Marraine', 'Mame1', 'Mame2', 'Mame3', 'Alumni'];

/** Taille maximale d'une photo de profil : 2 Mo. */
const PHOTO_TAILLE_MAX = 2 * 1024 * 1024;

// ---------------------------------------------------------------------------------------------
// Affichage et navigation
// ---------------------------------------------------------------------------------------------

/** Échappe une valeur avant de l'afficher dans le HTML (protection XSS). À utiliser pour TOUT affichage. */
function e($valeur): string
{
    return htmlspecialchars((string) $valeur, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Adresse complète d'une page du site : url('dut1/accueil.php'). */
function url(string $chemin = ''): string
{
    return BASE_URL . '/' . ltrim($chemin, '/');
}

/** Redirige vers une page du site puis arrête le script : rediriger('dut1/accueil.php'). */
function rediriger(string $chemin): void
{
    header('Location: ' . url($chemin));
    exit;
}

/** Enregistre un message à afficher sur la page suivante. $type : 'succes', 'erreur' ou 'info'. */
function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

/** Rend les messages en attente (appelé par includes/header.php) puis les efface. */
function afficher_flash(): string
{
    $html = '';
    foreach ($_SESSION['flash'] ?? [] as $f) {
        $html .= '<div class="alerte alerte-' . e($f['type']) . '">' . e($f['message']) . '</div>';
    }
    unset($_SESSION['flash']);
    return $html;
}

// ---------------------------------------------------------------------------------------------
// Formulaires : protection CSRF (tous les formulaires en POST)
// ---------------------------------------------------------------------------------------------

function jeton_csrf(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

/** À placer dans chaque <form method="post"> : <?= champ_csrf() ?> */
function champ_csrf(): string
{
    return '<input type="hidden" name="csrf" value="' . e(jeton_csrf()) . '">';
}

/** À appeler au début du traitement de chaque formulaire POST. */
function verifier_csrf(): void
{
    if (!hash_equals($_SESSION['csrf'] ?? '', (string) ($_POST['csrf'] ?? ''))) {
        http_response_code(400);
        exit('Formulaire expiré. Rechargez la page et réessayez.');
    }
}

// ---------------------------------------------------------------------------------------------
// Validation des données (format unique pour toutes les équipes)
// ---------------------------------------------------------------------------------------------

/** Nom d'utilisateur : minuscules, chiffres, « . » et « _ », 3 à 30 caractères. À appliquer avant d'enregistrer ET avant de chercher. */
function normaliser_nom_utilisateur(string $nom): string
{
    return strtolower(trim($nom));
}

function nom_utilisateur_valide(string $nom): bool
{
    return (bool) preg_match('/^[a-z0-9._]{3,30}$/', $nom);
}

/** Vrai si le nom d'utilisateur n'est pris ni par un DUT1, ni par un parrain, ni par un admin. */
function nom_utilisateur_disponible(PDO $pdo, string $nom): bool
{
    $sql = 'SELECT 1 FROM filleul WHERE nom_utilisateur = :a
            UNION SELECT 1 FROM parrain WHERE nom_utilisateur = :b
            UNION SELECT 1 FROM administrateur WHERE nom_utilisateur = :c';
    $req = $pdo->prepare($sql);
    $req->execute(['a' => $nom, 'b' => $nom, 'c' => $nom]);
    return $req->fetch() === false;
}

/** Mot de passe : au moins 8 caractères. */
function mot_de_passe_valide(string $mdp): bool
{
    return mb_strlen($mdp) >= 8;
}

/**
 * Téléphone stocké en chiffres seulement, avec l'indicatif : « 221771234567 ».
 * Accepte « 77 123 45 67 », « +221 77 123 45 67 », « 00221771234567 »… Renvoie null si invalide.
 */
function normaliser_telephone(string $tel): ?string
{
    $chiffres = preg_replace('/\D+/', '', $tel);
    if (str_starts_with($chiffres, '00')) {
        $chiffres = substr($chiffres, 2);
    }
    if (strlen($chiffres) === 9) {
        $chiffres = '221' . $chiffres; // numéro sénégalais saisi sans indicatif
    }
    return preg_match('/^\d{11,15}$/', $chiffres) ? $chiffres : null;
}

/** Lien WhatsApp à partir d'un téléphone stocké en base. */
function lien_whatsapp(string $telephone): string
{
    return 'https://wa.me/' . preg_replace('/\D+/', '', $telephone);
}

// ---------------------------------------------------------------------------------------------
// Photos de profil
// ---------------------------------------------------------------------------------------------

/**
 * Enregistre la photo envoyée par un champ <input type="file" name="photo">.
 * $prefixe : 'dut1' ou 'parrain'. Renvoie le chemin à stocker en base (« uploads/photos/… »),
 * null si aucun fichier n'a été envoyé. Lance une RuntimeException (message en français) si le fichier est refusé.
 */
function enregistrer_photo(?array $fichier, string $prefixe): ?string
{
    if ($fichier === null || ($fichier['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($fichier['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('La photo n’a pas pu être envoyée.');
    }
    if ($fichier['size'] > PHOTO_TAILLE_MAX) {
        throw new RuntimeException('La photo dépasse 2 Mo.');
    }
    $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $type = (new finfo(FILEINFO_MIME_TYPE))->file($fichier['tmp_name']);
    if (!isset($extensions[$type])) {
        throw new RuntimeException('La photo doit être en JPG, PNG ou WebP.');
    }
    $nom = $prefixe . '_' . bin2hex(random_bytes(8)) . '.' . $extensions[$type];
    if (!move_uploaded_file($fichier['tmp_name'], __DIR__ . '/../uploads/photos/' . $nom)) {
        throw new RuntimeException('La photo n’a pas pu être enregistrée.');
    }
    return 'uploads/photos/' . $nom;
}
