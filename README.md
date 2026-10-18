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
| `index.php` | Page d'entrée (choix DUT1 / parrain) | Commun |
| `config/db.example.php` | Modèle de configuration (base, `BASE_URL`) | Commun |
| `includes/init.php` | Démarrage de chaque page : session, `$pdo`, fonctions | Commun |
| `includes/fonctions.php` | Échappement, redirection, messages, CSRF, validations, photos | Commun |
| `includes/auth.php` | Identifiants, session, protection des pages selon le rôle | Commun |
| `includes/signatures.php` | Enregistrement d'une signature (refus des doublons), compteurs, rang, listes | Commun |
| `includes/header.php`, `includes/footer.php` | En-tête et pied de page | Commun |
| `assets/css/style.css`, `assets/js/main.js` | Style et script communs | Commun |
| `sql/signature2027.sql` | Création de la base | Commun |
| `espace/dut1.php`, `espace/parrain.php` | Accueil public de chaque espace (boutons « Créer un compte » / « Se connecter ») | **Équipes 1-2** |
| `assets/css/accueil.css`, `assets/js/accueil.js`, `assets/img/esp.jpg` | Style, menu et photo des pages `espace/` | **Équipes 1-2** |
| `inscription/dut1.php` | Inscription DUT1 | **Équipe 1** |
| `inscription/parrain.php` | Inscription parrains, marraines, Mames | **Équipe 1** |
| `inscription/fonctions_inscription.php` | Traitement commun aux deux inscriptions (validation, photo, enregistrement) | **Équipe 1** |
| `assets/css/inscription.css`, `assets/js/inscription.js` | Style et vérification en direct des formulaires d'inscription | **Équipe 1** |
| `connexion/dut1.php` | Connexion DUT1 | **Équipe 2** |
| `connexion/parrain.php` | Connexion parrains, marraines, Mames | **Équipe 2** |
| `connexion/fonctions_connexion.php` | Traitement commun aux deux connexions (limite de tentatives) | **Équipe 2** |
| `assets/css/connexion.css`, `assets/js/connexion.js` | Style et script des pages de connexion | **Équipe 2** |
| `connexion/deconnexion.php` | Déconnexion (tous les rôles) | **Équipe 2** |
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
| `dut1/signatures_api.php`, `assets/js/signatures_dut1.js` | API JSON et script de la liste des signatures (actualisation toutes les 30 s) | **Équipe 5** |
| `dut1/classement.php` | Classement de tous les DUT1, statistiques globales | **Équipe 5** |
| `dut1/classement_api.php`, `assets/js/classement.js` | API JSON et script du classement (actualisation toutes les 30 s) | **Équipe 5** |
| `dut1/signatures_fonctions.php` | Données des deux pages et outils de leurs API | **Équipe 5** |
| `dut1/signatures_api.md`, `dut1/signatures_exemple.json` | Documentation des API et exemple de réponse pour le front | **Équipe 5** |
| `admin/connexion.php` | Connexion administrateur | **Équipe 6** |
| `admin/fonctions_admin.php` | Fonctions partagées par les pages admin (lecture du compte visé, validation, champs) | **Équipe 6** |
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

État du travail et tâches restantes : [evolution.md](evolution.md). Revue du code et erreurs à éviter : [commentaire.md](commentaire.md).
