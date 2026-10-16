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
| `config/db.example.php` | Modèle de configuration (base, `BASE_URL`) | Commun |
| `includes/init.php` | Démarrage de chaque page : session, `$pdo`, fonctions | Commun |
| `includes/fonctions.php` | Échappement, redirection, messages, CSRF, validations, photos | Commun |
| `includes/auth.php` | Identifiants, session, protection des pages selon le rôle | Commun |
| `includes/signatures.php` | Enregistrement d'une signature (refus des doublons), compteurs, rang, listes | Commun |
| `includes/header.php`, `includes/footer.php` | En-tête et pied de page | Commun |
| `assets/css/style.css`, `assets/js/main.js` | Style et script communs | Commun |
| `sql/signature2027.sql` | Création de la base | Commun |
| `inscription/dut1.php` | Inscription DUT1 | **Équipe 1** |
| `inscription/parrain.php` | Inscription parrains, marraines, Mames | **Équipe 1** |
| `connexion/dut1.php` | Connexion DUT1 | **Équipe 2** |
| `connexion/parrain.php` | Connexion parrains, marraines, Mames | **Équipe 2** |
| `connexion/deconnexion.php` | Déconnexion | **Équipe 2** |
| `dut1/accueil.php` | Accueil DUT1 : compteur, bouton « Signer », QR code, checklist | **Équipe 3** |
| `dut1/signer.php` | Box de signature (sur le téléphone du DUT1) + messages | **Équipe 3** |
| `dut1/registre.php` | Registre des parrains avec leur chambre | **Équipe 3** |
| `dut1/telecharger.php` | Téléchargement de la liste des signataires | **Équipe 3** |
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

Tout est dans [CONTRIBUTING.md](CONTRIBUTING.md) : vocabulaire, modèle de page, conventions de nommage,
fonctions communes, Git et sécurité. À lire avant de coder.
