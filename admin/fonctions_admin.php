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
            $erreurs['type_parrain'] = 'Choisis un type.';
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
        $html .= '<option value="' . e($t) . '"' . ($t === $valeur ? ' selected' : '') . '>' . e(libelle_type($t)) . '</option>';
    }
    $html .= '</select>';
    if (isset($erreurs['type_parrain'])) {
        $html .= '<span class="admin-erreur-champ">' . e($erreurs['type_parrain']) . '</span>';
    }
    return $html . '</div>';
}

// ---------------------------------------------------------------------------------------------
// Mise en page de l'administration : barre latérale bleu nuit + contenu (maquette « Équipe 6 »)
// ---------------------------------------------------------------------------------------------

/** Icônes SVG (traits, 24 × 24), sans bibliothèque externe. */
function admin_icone(string $nom, int $taille = 20): string
{
    $traces = [
        'tableau'         => '<path d="M3 10.5 12 3l9 7.5V20a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1z"/>',
        'utilisateurs'    => '<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20a6.5 6.5 0 0 1 13 0"/><path d="M16 4.6a3.5 3.5 0 0 1 0 6.8M18 14.2A6.5 6.5 0 0 1 21.5 20"/>',
        'signatures'      => '<path d="M4 20h4L19 9a2.8 2.8 0 0 0-4-4L4 16z"/><path d="m13.5 6.5 4 4"/>',
        'classement'      => '<path d="M8 21h8M12 17v4M7 4h10v5a5 5 0 0 1-10 0z"/><path d="M7 6H4a3 3 0 0 0 3 4M17 6h3a3 3 0 0 1-3 4"/>',
        'telechargements' => '<path d="M12 3v12M7 10l5 5 5-5"/><path d="M4 17v3h16v-3"/>',
        'parametres'      => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.8-.3 1.7 1.7 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1.1-1.5 1.7 1.7 0 0 0-1.8.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.8 1.7 1.7 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.1a1.7 1.7 0 0 0 1.5-1.1 1.7 1.7 0 0 0-.3-1.8l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.8.3H9a1.7 1.7 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.5 1.7 1.7 0 0 0 1.8-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.8V9a1.7 1.7 0 0 0 1.5 1H21a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1z"/>',
        'deconnexion'     => '<path d="M15 4h4a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1h-4M10 17l5-5-5-5M15 12H3"/>',
        'personne'        => '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>',
        'groupe'          => '<circle cx="8" cy="9" r="3"/><circle cx="16" cy="9" r="3"/><path d="M2 20a6 6 0 0 1 12 0M10 20a6 6 0 0 1 12 0"/>',
        'valide'          => '<circle cx="12" cy="12" r="9"/><path d="m8 12.5 2.8 2.8L16.5 9.5"/>',
        'actifs'          => '<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20a6.5 6.5 0 0 1 13 0"/><path d="m16 11 2 2 4-4"/>',
        'ajouter'         => '<path d="M12 5v14M5 12h14"/>',
        'libelles'        => '<path d="M20.6 13.4 13.4 20.6a2 2 0 0 1-2.8 0L3 13V3h10l7.6 7.6a2 2 0 0 1 0 2.8z"/><circle cx="7.5" cy="7.5" r="1.5"/>',
    ];
    return '<svg class="admin-icone" width="' . $taille . '" height="' . $taille . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'
        . ($traces[$nom] ?? '') . '</svg>';
}

/** Entrées du menu latéral : clé => [libellé, page, icône]. */
const MENU_ADMIN = [
    'tableau'         => ['Tableau de bord', 'admin/index.php', 'tableau'],
    'utilisateurs'    => ['Utilisateurs', 'admin/utilisateurs.php', 'utilisateurs'],
    'signatures'      => ['Signatures', 'admin/signatures.php', 'signatures'],
    'classement'      => ['Classement', 'admin/classement.php', 'classement'],
    'telechargements' => ['Téléchargements', 'admin/telechargements.php', 'telechargements'],
    'libelles'        => ['Libellés des types', 'admin/libelles.php', 'libelles'],
    'parametres'      => ['Paramètres', 'admin/mot_de_passe.php', 'parametres'],
];

/** Début d'une page admin : en-tête commun, puis barre latérale. $actif : clé de MENU_ADMIN. */
function admin_debut(string $titre_page, string $actif): void
{
    require __DIR__ . '/../includes/header.php';
    echo '<div class="admin-cadre"><aside class="admin-barre"><nav>';
    foreach (MENU_ADMIN as $cle => [$libelle, $page, $icone]) {
        echo '<a class="admin-lien' . ($cle === $actif ? ' actif' : '') . '" href="' . e(url($page)) . '">'
            . admin_icone($icone) . '<span>' . e($libelle) . '</span></a>';
    }
    echo '<a class="admin-lien admin-lien-sortie" href="' . e(url('admin/deconnexion.php')) . '">'
        . admin_icone('deconnexion') . '<span>Déconnexion</span></a>';
    echo '</nav></aside><div class="admin-contenu">';
}

/** Fin d'une page admin. */
function admin_fin(): void
{
    echo '</div></div>';
    require __DIR__ . '/../includes/footer.php';
}
