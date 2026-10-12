# ==============================================================
#  Réorganise le dépôt Site Signature 2027 selon les 6 équipes
#  À lancer à la racine du dépôt (C:\xampp\htdocs\signature2027) :
#    powershell -ExecutionPolicy Bypass -File .\reorganiser_depot.ps1
# ==============================================================
$ErrorActionPreference = 'Stop'

if (-not (Test-Path '.git')) {
    Write-Host "Lancez ce script à la racine du dépôt (le dossier qui contient .git)." -ForegroundColor Red
    exit 1
}
if (git status --porcelain) {
    Write-Host "Il y a des modifications non enregistrées. Faites d'abord un commit, puis relancez." -ForegroundColor Red
    exit 1
}

# ---- 1. Dossiers ----
$dossiers = 'config','includes','assets/css','assets/js','assets/img','uploads/photos','sql',
            'inscription','connexion','dut1','parrain','admin'
foreach ($d in $dossiers) { New-Item -ItemType Directory -Force -Path $d | Out-Null }

# ---- 2. Déplacement des fichiers existants (git mv garde l'historique) ----
$deplacements = [ordered]@{
    'inscription_dut1.php'    = 'inscription/dut1.php'
    'inscription_parrain.php' = 'inscription/parrain.php'
    'connexion.php'           = 'connexion/dut1.php'
    'deconnexion.php'         = 'connexion/deconnexion.php'
    'accueil_dut1.php'        = 'dut1/accueil.php'
    'signer.php'              = 'dut1/signer.php'
    'telecharger.php'         = 'dut1/telecharger.php'
    'signatures_dut1.php'     = 'dut1/signatures.php'
    'accueil_parrain.php'     = 'parrain/accueil.php'
    'signatures_parrain.php'  = 'parrain/signatures.php'
    'admin/comptes.php'       = 'admin/utilisateurs.php'
}
foreach ($src in $deplacements.Keys) {
    $dst = $deplacements[$src]
    if ((Test-Path $src) -and -not (Test-Path $dst)) {
        git mv $src $dst
        Write-Host "Déplacé : $src  ->  $dst"
    }
}

# ---- 3. Nouveaux fichiers (vides, jamais écrasés) ----
$nouveaux = @(
    'includes/signatures.php',
    'assets/js/qrcode.js',
    'assets/js/scanner.js',
    'connexion/parrain.php',
    'dut1/registre.php',
    'parrain/scanner.php',
    'parrain/valider.php',
    'parrain/telecharger.php',
    'admin/connexion.php',
    'admin/utilisateur_creer.php',
    'admin/utilisateur_voir.php',
    'admin/utilisateur_modifier.php',
    'admin/utilisateur_supprimer.php',
    'admin/mot_de_passe.php',
    'admin/signatures.php'
)
foreach ($f in $nouveaux) {
    if (-not (Test-Path $f)) {
        New-Item -ItemType File -Path $f | Out-Null
        Write-Host "Créé : $f"
    }
}

# ---- 4. README avec la répartition ----
$readme = @'
# Site Signature 2027

> « Un lien aujourd'hui, une force demain ! »

Site de collecte des signatures des parrains, marraines et Mames par les DUT1 du département Informatique.
Stack : HTML, CSS, JavaScript, PHP, MySQL.

## Installation locale

1. Installer XAMPP et cloner le dépôt dans `C:\xampp\htdocs\signature2027`.
2. Dans phpMyAdmin, exécuter `sql/signature2027.sql`.
3. Copier `config/db.example.php` en `config/db.php` (ce fichier n'est pas versionné).
4. Ouvrir http://localhost/signature2027

## Arborescence et responsables

| Fichier | Rôle | Équipe |
|---|---|---|
| `index.php` | Page d'entrée (liens inscription / connexion) | Commun |
| `config/db.example.php` | Modèle de connexion PDO | Commun |
| `includes/header.php`, `includes/footer.php` | En-tête et pied de page | Commun |
| `assets/css/style.css`, `assets/js/main.js` | Style et script communs | Commun |
| `sql/signature2027.sql` | Création de la base | Commun |
| `inscription/dut1.php` | Inscription DUT1 | **Équipe 1** |
| `inscription/parrain.php` | Inscription parrains, marraines, Mames | **Équipe 1** |
| `connexion/dut1.php` | Connexion DUT1 | **Équipe 2** |
| `connexion/parrain.php` | Connexion parrains, marraines, Mames | **Équipe 2** |
| `connexion/deconnexion.php` | Déconnexion | **Équipe 2** |
| `includes/auth.php` | Session et protection des pages selon le rôle | **Équipe 2** |
| `dut1/accueil.php` | Accueil DUT1 : compteur, bouton « Signer », QR code, checklist | **Équipe 3** |
| `dut1/signer.php` | Box de signature (sur le téléphone du DUT1) + messages | **Équipe 3** |
| `dut1/registre.php` | Registre des parrains avec leur chambre | **Équipe 3** |
| `dut1/telecharger.php` | Téléchargement de la liste des signataires | **Équipe 3** |
| `includes/signatures.php` | Fonction d'enregistrement d'une signature (refus des doublons) | **Équipe 3** (utilisée par l'Équipe 4) |
| `assets/js/qrcode.js` | Génération du QR code du DUT1 | **Équipe 3** |
| `parrain/accueil.php` | Accueil des signataires : nombre de signatures | **Équipe 4** |
| `parrain/signatures.php` | Personnes pour qui le signataire a signé | **Équipe 4** |
| `parrain/scanner.php` | Scan du QR code d'un DUT1 | **Équipe 4** |
| `parrain/valider.php` | Validation de la signature après le scan | **Équipe 4** |
| `parrain/telecharger.php` | Téléchargement de la liste | **Équipe 4** |
| `assets/js/scanner.js` | Lecture du QR code avec la caméra | **Équipe 4** |
| `dut1/signatures.php` | Liste des signatures DUT1 : WhatsApp, rang, nombre | **Équipe 5** |
| `admin/connexion.php` | Connexion administrateur | **Équipe 6** |
| `admin/index.php` | Tableau de bord | **Équipe 6** |
| `admin/utilisateurs.php` | Liste de tous les utilisateurs | **Équipe 6** |
| `admin/utilisateur_creer.php` | Création d'un utilisateur | **Équipe 6** |
| `admin/utilisateur_voir.php` | Fiche d'un utilisateur | **Équipe 6** |
| `admin/utilisateur_modifier.php` | Modification d'un utilisateur | **Équipe 6** |
| `admin/utilisateur_supprimer.php` | Suppression d'un utilisateur | **Équipe 6** |
| `admin/mot_de_passe.php` | Réinitialisation d'un mot de passe | **Équipe 6** |
| `admin/signatures.php` | Ajout / retrait de signatures d'un DUT1 | **Équipe 6** |

## Fonctionnement de la signature

Les deux chemins enregistrent la signature avec la même fonction, `includes/signatures.php` :

- **Sur le téléphone du DUT1** : le DUT1 ouvre `dut1/signer.php`, le signataire saisit son nom d'utilisateur et son mot de passe.
- **Par QR code** : le QR code du DUT1 contient l'adresse `parrain/valider.php?dut1=<id>`. Le signataire, connecté sur son propre téléphone, le scanne avec `parrain/scanner.php` et confirme.

Un signataire ne peut signer qu'une fois pour un même DUT1 : la base refuse le doublon (erreur MySQL 1062).

## Règles de travail en équipe

- Une branche par fonctionnalité : `equipe1/inscription-dut1`, `equipe3/qrcode`, etc. Jamais de travail direct sur `main`.
- Chaque équipe ne modifie que ses fichiers. Pour un fichier **Commun**, prévenir les autres équipes et passer par une pull request.
- Faire `git pull origin main` avant de commencer à travailler.
- Pas de `git push --force` sur `main`.
- Ne jamais commiter `config/db.php` ni le contenu de `uploads/photos/`.
- Toujours utiliser des requêtes préparées (PDO), `password_hash()` / `password_verify()` et `htmlspecialchars()` à l'affichage.
'@
[IO.File]::WriteAllText((Join-Path (Get-Location) 'README.md'), $readme, (New-Object System.Text.UTF8Encoding $false))
Write-Host "README.md mis à jour"

# ---- 5. Commit ----
git add -A
git commit -m "Reorganisation du depot selon les 6 equipes"
Write-Host ""
Write-Host "Terminé. Vérifiez avec 'git status', puis envoyez avec : git push" -ForegroundColor Green
