<?php
/**
 * Fonctions propres à l'administration (Équipe 6). Incluses par les pages du dossier admin/.
 * Les comptes gérés ici sont ceux des DUT1 ('dut1') et des parrains ('parrain').
 */

const ROLES_GERES = ['dut1' => 'DUT1', 'parrain' => 'Parrain, marraine ou Mame'];

/**
 * Lit le compte visé par ?role=…&id=… (ou les mêmes champs en POST).
 * Redirige vers la liste des utilisateurs si le compte n'existe pas.
 */
function admin_lire_cible(PDO $pdo): array
{
    $role = (string) ($_POST['role'] ?? $_GET['role'] ?? '');
    $id = (int) ($_POST['id'] ?? $_GET['id'] ?? 0);
    if (!isset(ROLES_GERES[$role]) || $id <= 0) {
        flash('erreur', 'Compte introuvable.');
        rediriger('admin/utilisateurs.php');
    }
    $r = ROLES[$role];
    $req = $pdo->prepare('SELECT * FROM ' . $r['table'] . ' WHERE ' . $r['id'] . ' = ?');
    $req->execute([$id]);
    $compte = $req->fetch();
    if (!$compte) {
        flash('erreur', 'Compte introuvable.');
        rediriger('admin/utilisateurs.php');
    }
    $compte['role'] = $role;
    $compte['id'] = $id;
    return $compte;
}

/** Adresse d'une page admin pour un compte : admin_url('utilisateur_voir.php', 'dut1', 4). */
function admin_url(string $page, string $role, int $id): string
{
    return url('admin/' . $page . '?role=' . urlencode($role) . '&id=' . $id);
}

/**
 * Valide les champs d'un compte (création ou modification).
 * $actuel : nom d'utilisateur actuel du compte en modification (il reste autorisé), null en création.
 * Renvoie [valeurs nettoyées, erreurs par champ].
 */
function admin_valider_compte(PDO $pdo, string $role, array $post, ?string $actuel = null): array
{
    $v = [
        'nom'             => trim((string) ($post['nom'] ?? '')),
        'prenom'          => trim((string) ($post['prenom'] ?? '')),
        'nom_utilisateur' => normaliser_nom_utilisateur((string) ($post['nom_utilisateur'] ?? '')),
        'telephone'       => normaliser_telephone((string) ($post['telephone'] ?? '')),
    ];
    $erreurs = [];
    if ($v['nom'] === '' || mb_strlen($v['nom']) > 50) {
        $erreurs['nom'] = 'Le nom est obligatoire (50 caractères au plus).';
    }
    if ($v['prenom'] === '' || mb_strlen($v['prenom']) > 80) {
        $erreurs['prenom'] = 'Le prénom est obligatoire (80 caractères au plus).';
    }
    if (!nom_utilisateur_valide($v['nom_utilisateur'])) {
        $erreurs['nom_utilisateur'] = 'De 3 à 30 caractères : lettres minuscules, chiffres, « . » ou « _ ».';
    } elseif ($v['nom_utilisateur'] !== $actuel && !nom_utilisateur_disponible($pdo, $v['nom_utilisateur'])) {
        $erreurs['nom_utilisateur'] = 'Ce nom d’utilisateur est déjà pris.';
    }
    if ($v['telephone'] === null) {
        $erreurs['telephone'] = 'Numéro invalide (ex. 77 123 45 67 ou +221 77 123 45 67).';
    }
    if ($role === 'parrain') {
        $v['type_parrain'] = (string) ($post['type_parrain'] ?? '');
        $v['chambre'] = trim((string) ($post['chambre'] ?? ''));
        if (!in_array($v['type_parrain'], TYPES_PARRAIN, true)) {
            $erreurs['type_parrain'] = 'Choisissez un type.';
        }
        if ($v['chambre'] === '' || mb_strlen($v['chambre']) > 30) {
            $erreurs['chambre'] = 'La chambre est obligatoire (30 caractères au plus).';
        }
    }
    return [$v, $erreurs];
}

/** Valide un nouveau mot de passe et sa confirmation. Renvoie un message d'erreur ou null. */
function admin_erreur_mot_de_passe(array $post): ?string
{
    $mdp = (string) ($post['mot_de_passe'] ?? '');
    if (!mot_de_passe_valide($mdp)) {
        return 'Le mot de passe doit faire au moins 8 caractères.';
    }
    if ($mdp !== (string) ($post['confirmation_mot_de_passe'] ?? '')) {
        return 'Les deux mots de passe ne correspondent pas.';
    }
    return null;
}

/** Supprime une photo de profil du disque (seulement dans uploads/photos/). */
function admin_supprimer_photo(?string $chemin): void
{
    if ($chemin && str_starts_with($chemin, 'uploads/photos/') && !str_contains($chemin, '..')) {
        $fichier = __DIR__ . '/../' . $chemin;
        if (is_file($fichier)) {
            unlink($fichier);
        }
    }
}

/** Champ de formulaire avec son message d'erreur. */
function admin_champ(string $nom, string $libelle, string $valeur, array $erreurs, string $attributs = ''): string
{
    $html = '<div class="champ"><label for="' . e($nom) . '">' . e($libelle) . '</label>'
        . '<input id="' . e($nom) . '" name="' . e($nom) . '" value="' . e($valeur) . '" ' . $attributs . '>';
    if (isset($erreurs[$nom])) {
        $html .= '<span class="admin-erreur-champ">' . e($erreurs[$nom]) . '</span>';
    }
    return $html . '</div>';
}

/** Liste déroulante du type de parrain. */
function admin_choix_type(string $valeur, array $erreurs): string
{
    $html = '<div class="champ"><label for="type_parrain">Type</label><select id="type_parrain" name="type_parrain" required>'
        . '<option value="">Choisir…</option>';
    foreach (TYPES_PARRAIN as $t) {
        $html .= '<option value="' . e($t) . '"' . ($t === $valeur ? ' selected' : '') . '>' . e($t) . '</option>';
    }
    $html .= '</select>';
    if (isset($erreurs['type_parrain'])) {
        $html .= '<span class="admin-erreur-champ">' . e($erreurs['type_parrain']) . '</span>';
    }
    return $html . '</div>';
}
