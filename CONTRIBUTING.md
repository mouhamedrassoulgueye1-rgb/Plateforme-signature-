# Règles communes du projet Signature 2027

Ce fichier est le contrat entre les 6 équipes. Tout le monde le lit avant d'écrire la première ligne.
Une règle ne change que par une pull request acceptée par le groupe.

## 1. Ce que le groupe fait AVANT que les équipes codent

| # | Tâche | Qui |
|---|---|---|
| 1 | Valider ce fichier et les fichiers **Commun** (branche `commun/normalisation`), puis les fusionner dans `main` | Tout le groupe, chef de projet pour la fusion |
| 2 | Protéger `main` sur GitHub : *Settings › Branches › Add rule* : `main`, cocher « Require a pull request before merging » (1 approbation) | Chef de projet (propriétaire du dépôt) |
| 3 | Ajouter chaque membre comme collaborateur (*Settings › Collaborators*) | Chef de projet |
| 4 | Installer XAMPP (PHP 8.1 ou plus), Git, VS Code + extension « EditorConfig » | Chaque membre |
| 5 | Régler Git une fois : `git config --global user.name "Prénom Nom"`, `git config --global user.email "mail@github"`, `git config --global core.autocrlf input` | Chaque membre |
| 6 | Cloner dans `C:\xampp\htdocs\signature2027`, créer la base (`sql/signature2027.sql`), copier `config/db.example.php` en `config/db.php`, ouvrir http://localhost/signature2027 | Chaque membre |
| 7 | Créer un compte administrateur de test (commande en bas de `sql/signature2027.sql`) | Équipe 6 |

Tant que l'étape 1 n'est pas fusionnée, aucune équipe ne commence ses pages : elles en dépendent toutes.

## 2. Vocabulaire unique

| On dit | Dans la base | Rôle (session, code) | Dossier des pages |
|---|---|---|---|
| DUT1 (filleul) | table `filleul`, clé `id_filleul` | `'dut1'` | `dut1/` |
| Parrain, marraine, Mame, alumni (signataire) | table `parrain`, clé `id_parrain`, `type_parrain` | `'parrain'` | `parrain/` |
| Administrateur | table `administrateur`, clé `id_admin` | `'admin'` | `admin/` |
| Signature | table `signature` (`id_parrain`, `id_filleul`, `date_signature`) | | |

Valeurs de `type_parrain`, écrites exactement ainsi : `Parrain`, `Marraine`, `Mame1`, `Mame2`, `Mame3`, `Alumni` (constante `TYPES_PARRAIN`).

## 3. Modèle de page (à copier)

```php
<?php
require_once __DIR__ . '/../includes/init.php';
$moi = exiger_role('dut1');                // page réservée ; supprimer pour une page publique

$erreurs = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifier_csrf();
    // 1. lire et nettoyer : $nom = trim($_POST['nom'] ?? '');
    // 2. valider, remplir $erreurs
    // 3. si pas d'erreur : requête préparée, flash('succes', '…'), rediriger('dut1/accueil.php');
}

$titre_page = 'Titre de la page';
require __DIR__ . '/../includes/header.php';
?>
<section class="carte">
    <h1><?= e($titre_page) ?></h1>
    <form class="formulaire" method="post" enctype="multipart/form-data">
        <?= champ_csrf() ?>
        <div class="champ">
            <label for="nom">Nom</label>
            <input id="nom" name="nom" required value="<?= e($_POST['nom'] ?? '') ?>">
        </div>
        <button class="btn">Valider</button>
    </form>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
```

Règle : le traitement PHP en haut, le HTML en bas. Après un POST réussi, toujours `rediriger()` (pas de double envoi en actualisant).

## 4. Conventions de nommage

| Élément | Convention | Exemple |
|---|---|---|
| Fichiers et dossiers | minuscules, `snake_case`, français | `utilisateur_modifier.php` |
| Tables et colonnes | singulier, `snake_case` | `filleul.nom_utilisateur` |
| Variables et fonctions PHP | `snake_case`, fonctions = verbe | `$id_filleul`, `enregistrer_photo()` |
| Constantes PHP | `MAJUSCULES` | `TYPES_PARRAIN` |
| Champs de formulaire (`name`) | **le nom de la colonne** | `nom`, `prenom`, `nom_utilisateur`, `telephone`, `chambre`, `type_parrain`, `mot_de_passe`, `confirmation_mot_de_passe`, `photo` |
| Paramètres d'URL | nom du rôle ou de la clé | `parrain/valider.php?dut1=12`, `admin/utilisateur_voir.php?role=parrain&id=4` |
| Classes CSS | `kebab-case` ; classes communes de `style.css`, sinon préfixées par la page | `.btn`, `.accueil-dut1-compteur` |
| JavaScript | `camelCase`, un fichier par fonctionnalité, pas de script dans le HTML | `assets/js/qrcode.js` |
| Messages à l'utilisateur | `flash('succes', '…')` ; types : `succes`, `erreur`, `info` | |

Formats des données (fonctions dans `includes/fonctions.php`) :

- **Nom d'utilisateur** : minuscules, chiffres, `.` et `_`, 3 à 30 caractères, unique sur les trois tables (`normaliser_nom_utilisateur()`, `nom_utilisateur_valide()`, `nom_utilisateur_disponible()`).
- **Téléphone** : stocké en chiffres avec l'indicatif, `221771234567` (`normaliser_telephone()`), lien par `lien_whatsapp()`.
- **Mot de passe** : 8 caractères au moins, `password_hash($mdp, PASSWORD_DEFAULT)` à l'enregistrement, jamais stocké en clair.
- **Photo** : JPG, PNG ou WebP, 2 Mo au plus, enregistrée par `enregistrer_photo()` ; la base garde le chemin `uploads/photos/…`.

## 5. Ce que chaque équipe fournit aux autres

Ces fonctions sont communes : on les appelle, on ne les réécrit pas. Pour en changer une, pull request **Commun**.

| Fonction | Fichier | Utilisée par |
|---|---|---|
| `verifier_identifiants()`, `ouvrir_session()`, `exiger_role()`, `fermer_session()`, `utilisateur()` | `includes/auth.php` | Équipes 2, 3, 6 et toutes les pages protégées |
| `enregistrer_signature()` (renvoie `ok`, `deja_signe`, `introuvable` ou `suspendu`) + `MESSAGES_SIGNATURE` | `includes/signatures.php` | Équipe 3 (box), Équipe 4 (QR code), Équipe 6 (ajout manuel) |
| `nb_signatures_filleul()`, `rang_filleul()`, `nb_filleuls_actifs()`, `signataires_du_filleul()` | `includes/signatures.php` | Équipes 3 et 5 |
| `nb_signatures_parrain()`, `filleuls_signes_par()` | `includes/signatures.php` | Équipe 4 |
| `e()`, `url()`, `rediriger()`, `flash()`, CSRF, validations, `enregistrer_photo()` | `includes/fonctions.php` | Tout le monde |

Après une connexion réussie, on arrive sur : DUT1 → `dut1/accueil.php`, parrain → `parrain/accueil.php`, admin → `admin/index.php` (constante `ROLES`).

## 6. Travail avec Git et GitHub

1. Avant de commencer : `git switch main` puis `git pull origin main`.
2. Une branche par tâche, nommée `equipeN/sujet` : `git switch -c equipe1/inscription-dut1`. Jamais de travail sur `main`.
3. Des petits commits, message au présent qui dit ce qui change, préfixé par l'équipe :
   `[Équipe 1] Ajoute la validation du téléphone à l'inscription DUT1`
4. Envoyer : `git push -u origin equipe1/inscription-dut1`, puis ouvrir une **pull request** vers `main` sur GitHub et remplir la liste de vérification.
5. Relecture par **une personne d'une autre équipe**, puis fusion par le chef de projet (« Squash and merge »). On supprime la branche après la fusion.
6. Conflit : `git pull origin main` dans sa branche, corriger les fichiers marqués, `git add`, `git commit`, `git push`. En cas de doute sur un fichier qui n'est pas le sien, demander à l'équipe propriétaire.

Interdit : `git push --force` sur `main`, commiter `config/db.php` ou des photos, modifier le fichier d'une autre équipe sans la prévenir.

## 7. Sécurité (obligatoire, vérifié en relecture)

- Requêtes SQL toujours préparées (`$pdo->prepare()` puis `execute()`), aucune variable collée dans le texte SQL.
- Tout affichage passe par `e()` (protection XSS).
- Chaque formulaire POST : `champ_csrf()` dans le formulaire, `verifier_csrf()` au traitement.
- Pages protégées : `exiger_role()` en deuxième ligne.
- Validation côté serveur même si le HTML a `required` : le navigateur ne protège rien.
- En production : HTTPS (Let's Encrypt), `config/db.php` avec un utilisateur MySQL dédié, jamais `root`.
