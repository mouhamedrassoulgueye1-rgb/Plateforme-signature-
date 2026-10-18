<?php
/**
 * Équipes 1-2 : traitement commun aux pages inscription/dut1.php et inscription/parrain.php.
 *
 * Formats et règles : ceux de includes/fonctions.php (CONTRIBUTING.md, §4), pour que l'inscription,
 * la connexion et l'administration acceptent exactement les mêmes données.
 * Champs attendus (attribut name = nom de la colonne) :
 *   nom, prenom, nom_utilisateur, telephone, mot_de_passe, confirmation_mot_de_passe, photo
 *   type_parrain, chambre      uniquement pour un parrain
 */

/** Lettres (accents compris), espaces, apostrophes, points et tirets. */
const REGEX_NOM_PERSONNE = "/^\p{L}[\p{L}\p{M} '.-]*$/u";

/** Le DUT1 doit mettre sa photo (les parrains la reconnaissent) ; elle est facultative pour un parrain. */
const PHOTO_OBLIGATOIRE = ['dut1' => true, 'parrain' => false];

/**
 * Traite le formulaire d'inscription d'un rôle ('dut1' ou 'parrain').
 * Si tout est bon : crée le compte, puis redirige vers la page de connexion du rôle.
 * Sinon : renvoie les erreurs par champ (['telephone' => 'Numéro invalide…', …]).
 */
function traiter_inscription(PDO $pdo, string $role): array
{
    verifier_csrf();
    $texte = fn(string $cle): string => is_string($_POST[$cle] ?? null) ? trim($_POST[$cle]) : '';

    $v = [
        'nom'             => $texte('nom'),
        'prenom'          => $texte('prenom'),
        'nom_utilisateur' => normaliser_nom_utilisateur($texte('nom_utilisateur')),
        'telephone'       => normaliser_telephone($texte('telephone')),
    ];
    $mdp = (string) ($_POST['mot_de_passe'] ?? '');
    $erreurs = [];

    if ($v['nom'] === '' || mb_strlen($v['nom']) > 50 || !preg_match(REGEX_NOM_PERSONNE, $v['nom'])) {
        $erreurs['nom'] = 'Nom obligatoire : lettres seulement, 50 caractères au plus.';
    }
    if ($v['prenom'] === '' || mb_strlen($v['prenom']) > 80 || !preg_match(REGEX_NOM_PERSONNE, $v['prenom'])) {
        $erreurs['prenom'] = 'Prénom obligatoire : lettres seulement, 80 caractères au plus.';
    }
    if (!nom_utilisateur_valide($v['nom_utilisateur'])) {
        $erreurs['nom_utilisateur'] = 'De 3 à 30 caractères : lettres minuscules, chiffres, « . » ou « _ ».';
    } elseif (!nom_utilisateur_disponible($pdo, $v['nom_utilisateur'])) {
        $erreurs['nom_utilisateur'] = 'Ce nom d’utilisateur est déjà utilisé. Choisis-en un autre.';
    }
    if ($v['telephone'] === null) {
        $erreurs['telephone'] = 'Numéro invalide (ex. 77 123 45 67 ou +221 77 123 45 67).';
    }
    if (!mot_de_passe_valide($mdp)) {
        $erreurs['mot_de_passe'] = 'Le mot de passe doit faire au moins 8 caractères.';
    } elseif (strlen($mdp) > 72) {
        $erreurs['mot_de_passe'] = '72 caractères au plus.'; // limite de bcrypt : au-delà, le mot de passe serait tronqué
    }
    if ($mdp !== (string) ($_POST['confirmation_mot_de_passe'] ?? '')) {
        $erreurs['confirmation_mot_de_passe'] = 'Les deux mots de passe ne sont pas identiques.';
    }

    if ($role === 'parrain') {
        $v['type_parrain'] = $texte('type_parrain');
        $v['chambre'] = $texte('chambre');
        if (!in_array($v['type_parrain'], TYPES_PARRAIN, true)) {
            $erreurs['type_parrain'] = 'Choisis ton type.';
        }
        if ($v['chambre'] === '' || mb_strlen($v['chambre']) > 30) {
            $erreurs['chambre'] = 'La chambre est obligatoire (30 caractères au plus).';
        }
    }

    $fichier = $_FILES['photo'] ?? null;
    if (PHOTO_OBLIGATOIRE[$role] && ($fichier['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        $erreurs['photo'] = 'Choisis une photo de profil.';
    }
    if ($erreurs) {
        return $erreurs;
    }

    try {
        $photo = enregistrer_photo($fichier, $role);
    } catch (RuntimeException $ex) {
        return ['photo' => $ex->getMessage()];
    }

    $hash = password_hash($mdp, PASSWORD_DEFAULT);
    try {
        if ($role === 'dut1') {
            $req = $pdo->prepare('INSERT INTO filleul (nom, prenom, nom_utilisateur, telephone, photo, mot_de_passe) VALUES (?, ?, ?, ?, ?, ?)');
            $req->execute([$v['nom'], $v['prenom'], $v['nom_utilisateur'], $v['telephone'], $photo, $hash]);
        } else {
            $req = $pdo->prepare('INSERT INTO parrain (nom, prenom, nom_utilisateur, telephone, type_parrain, chambre, photo, mot_de_passe) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
            $req->execute([$v['nom'], $v['prenom'], $v['nom_utilisateur'], $v['telephone'], $v['type_parrain'], $v['chambre'], $photo, $hash]);
        }
    } catch (PDOException $ex) {
        if ($photo !== null && is_file(__DIR__ . '/../' . $photo)) {
            unlink(__DIR__ . '/../' . $photo);
        }
        // 1062 = doublon : quelqu'un a pris ce nom d'utilisateur entre-temps
        if ((int) ($ex->errorInfo[1] ?? 0) === 1062) {
            return ['nom_utilisateur' => 'Ce nom d’utilisateur est déjà utilisé. Choisis-en un autre.'];
        }
        throw $ex;
    }

    flash('succes', 'Compte créé ! Connecte-toi avec ton nom d’utilisateur et ton mot de passe.');
    rediriger(ROLES[$role]['connexion']);
    return [];
}

/** Message d'erreur d'un champ, à placer dans <p class="message-erreur" id="err-…">. */
function erreur_champ(array $erreurs, string $champ): string
{
    return e($erreurs[$champ] ?? '');
}

/** Valeur saisie à réafficher après une erreur (jamais pour un mot de passe ni une photo). */
function valeur_saisie(string $champ): string
{
    return e(is_string($_POST[$champ] ?? null) ? $_POST[$champ] : '');
}
