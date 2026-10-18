# État d'avancement — Signature 2027

Point fait le 18/10/2026, sur la branche **`commun/uniformisation`** (= `main` + branches en attente fusionnées + corrections).
Les erreurs et critiques détaillées sont dans [commentaire.md](commentaire.md).

## Vue d'ensemble

| Équipe | Sujet | Avancement | État |
|---|---|---|---|
| Commun | Socle (`includes/`, base, règles) | ██████████ 100 % | Terminé, corrigé (déconnexion) |
| 1 | Inscription DUT1 / parrains | █████████░ 90 % | Fonctionne ; reste le choix de maquette (§ À décider) |
| 2 | Connexion, déconnexion | █████████░ 90 % | Fonctionne ; idem |
| 3 | Espace DUT1 (accueil, signer, registre, téléchargement, QR code) | ██░░░░░░░░ 20 % | Seul le registre existe |
| 4 | Espace parrain (accueil, scanner, valider, signatures, téléchargement) | ░░░░░░░░░░ 0 % | Rien commencé |
| 5 | Mes signatures, Classement | ██████████ 100 % | Terminé, fusionné dans `commun/uniformisation` |
| 6 | Administration | █████████░ 90 % | En ligne : tout le CRUD ; en local seulement : barre latérale, classement, exports CSV |

**Le parcours principal ne marche pas encore de bout en bout** : après la connexion, un DUT1 arrive sur
`dut1/accueil.php` et un parrain sur `parrain/accueil.php`, qui sont **vides** (Équipes 3 et 4). Et aucune signature
ne peut être faite par un utilisateur : `dut1/signer.php`, `parrain/scanner.php` et `parrain/valider.php` sont vides
(seul l'admin peut en ajouter).

## Ce qui fonctionne (testé le 18/10 sur une base de test avec 120 DUT1, 45 parrains, 2 098 signatures)

- `index.php` → `espace/dut1.php` / `espace/parrain.php` → inscription ou connexion.
- Inscription DUT1 (photo obligatoire) et parrain (type, chambre) : validation serveur, erreurs sous chaque champ,
  refus des doublons de nom d'utilisateur, CSRF, photo enregistrée dans `uploads/photos/`.
- Connexion DUT1 / parrain : un compte ne peut ouvrir que l'espace de son rôle ; 5 essais puis 60 s d'attente ;
  compte suspendu refusé ; message après déconnexion.
- Pages protégées : redirection vers la bonne connexion ; un compte suspendu par l'admin est déconnecté à la page suivante.
- `dut1/registre.php` : liste des signataires actifs, coche « a signé », filtres pavillon / « pas encore signé ».
- `dut1/signatures.php`, `dut1/classement.php` et leurs API JSON (actualisation toutes les 30 s).
- Administration : tableau de bord, liste filtrée, création, fiche, modification, suspension, suppression,
  mots de passe, ajout / retrait de signatures (doublon refusé).

## Ce qui reste à faire

### Équipe 3 — espace DUT1 (priorité 1 : bloque le parcours)
- [ ] `dut1/accueil.php` : compteur (`nb_signatures_filleul()`), rang (`rang_filleul()` / `nb_filleuls_actifs()`),
      bouton « Signer », QR code, liens vers registre / mes signatures / classement.
- [ ] `dut1/signer.php` : box de signature sur le téléphone du DUT1 → `verifier_identifiants($pdo, 'parrain', …)`
      puis `enregistrer_signature()` et `MESSAGES_SIGNATURE`.
- [ ] `assets/js/qrcode.js` : QR code de l'adresse `url('parrain/valider.php?dut1=' . $moi['id'])`.
- [ ] `dut1/telecharger.php` : liste des signataires (`signataires_du_filleul()`), en CSV par exemple.
- [ ] `dut1/registre.php` : remplacer l'affichage provisoire par la maquette de l'équipe si elle le souhaite
      (avec `header.php` / `footer.php`, classes préfixées `registre-`, script dans `assets/js/`).

### Équipe 4 — espace parrain (priorité 1 : bloque le parcours)
- [ ] `parrain/accueil.php` : nombre de signatures données (`nb_signatures_parrain()`).
- [ ] `parrain/scanner.php` + `assets/js/scanner.js` : lecture du QR code à la caméra.
- [ ] `parrain/valider.php?dut1=<id>` : confirmation puis `enregistrer_signature()` (formulaire POST + CSRF).
- [ ] `parrain/signatures.php` : DUT1 signés (`filleuls_signes_par()`).
- [ ] `parrain/telecharger.php`.

### Équipe 6 — administration
- [ ] Envoyer le travail resté en local (stash `stash@{0}` + `admin/classement.php`, `admin/telechargements.php`)
      **ensemble** : les deux pages appellent `admin_debut()` / `admin_icone()` qui ne sont que dans le stash.
- [ ] Passer la modification de `includes/header.php` (dans le stash) par une PR Commun.
- [ ] Retirer `session_start()` de `admin/deconnexion.php`, ou supprimer ce fichier (doublon de `connexion/deconnexion.php`).
- [ ] Commiter `sql/donnees_test.sql` (base de test partagée, avec l'avertissement `DELETE FROM` en tête).

### Équipes 1-2
- [ ] Nommer les variables JS en français dans `assets/js/accueil.js` (`toggleMenu`, `burger`, `show`) ;
      `connexion.js` et `inscription.js` le sont.
- [ ] Selon la décision du groupe sur la maquette (ci-dessous), préfixer les classes CSS (`connexion-`, `espace-`).

### Équipe 5
- [ ] Retirer ou remplacer la colonne « Promotion » (valeur fixe 2027).
- [ ] Mettre à jour `signatures_api.md` : la section « Tester en local » n'est plus utile (la connexion DUT1 existe).

### Commun
- [ ] Une seule fonction de classement (`classement_filleuls()` dans `includes/signatures.php`) utilisée par l'Équipe 5
      et l'admin, avec la même règle sur les comptes suspendus.
- [ ] Déplacer `OBJECTIF_SIGNATURES` (Équipe 5) dans `includes/signatures.php` si l'accueil DUT1 l'utilise.
- [ ] `init.php` : message neutre si la base est injoignable (pas de trace avec les identifiants).
- [ ] Message clair quand un utilisateur connecté ouvre une page d'un autre rôle (`exiger_role()`).
- [ ] Avant la mise en ligne : HTTPS, `config/db.php` avec un utilisateur MySQL dédié, `display_errors = Off`.

## À décider par le groupe

1. **Une seule maquette** : celle des équipes 1-2 (Poppins, bleu nuit) généralisée dans `style.css` / `header.php`,
   ou `style.css` actuel partout. Aujourd'hui le site change d'apparence entre les pages publiques et les autres.
2. **Tutoiement ou vouvoiement** dans tous les messages.
3. **Photo obligatoire pour les DUT1** (choix des équipes 1-2) alors que l'admin peut créer un DUT1 sans photo.
4. **Objectif de signatures** (120 aujourd'hui, choisi par l'Équipe 5).
5. Libellés affichés des types (`Mame1` → « Mame 1 » ?) : à ajouter à côté de `TYPES_PARRAIN`.

## Git : étapes pour repartir proprement

1. Relire puis fusionner `commun/uniformisation` dans `main` (pull request, une relecture d'une autre équipe).
2. Supprimer les branches fusionnées ou abandonnées : `commun/normalisation`, `commun/photo-signataires`,
   `equipe5/signatures-dut1`, `equipe6/bases`, `revert-1-patch-4`.
3. Protéger `main` (CONTRIBUTING §1, étape 2) : plus aucune fusion sans approbation.
4. Chaque équipe : `git switch main`, `git pull origin main`, puis une nouvelle branche `equipeN/sujet`.
