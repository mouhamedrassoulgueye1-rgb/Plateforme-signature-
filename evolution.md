# État d'avancement — Signature 2027

Point fait le 18/10/2026 (mis à jour après la maquette unique), sur la branche **`commun/maquette-unique`**.
Les erreurs et critiques détaillées sont dans [commentaire.md](commentaire.md).

## Vue d'ensemble

| Équipe | Sujet | Avancement | État |
|---|---|---|---|
| Commun | Socle (`includes/`, base, règles, maquette) | ██████████ 100 % | Terminé : maquette unique, tutoiement, classement unique, libellés |
| 1 | Inscription DUT1 / parrains | ██████████ 100 % | Terminé (photo obligatoire pour les DUT1) |
| 2 | Connexion, déconnexion | ██████████ 100 % | Terminé |
| 3 | Espace DUT1 (accueil, signer, registre, téléchargement, QR code) | ██░░░░░░░░ 20 % | Seul le registre existe |
| 4 | Espace parrain (accueil, scanner, valider, signatures, téléchargement) | ░░░░░░░░░░ 0 % | Rien commencé |
| 5 | Mes signatures, Classement | ██████████ 100 % | Terminé, au format commun |
| 6 | Administration | ██████████ 100 % | Terminé : CRUD, barre latérale, classement, exports CSV, libellés des types |

**Le parcours principal ne marche pas encore de bout en bout** : après la connexion, un DUT1 arrive sur
`dut1/accueil.php` et un parrain sur `parrain/accueil.php`, qui sont **vides** (Équipes 3 et 4). Aucune signature
ne peut être faite par un utilisateur : `dut1/signer.php`, `parrain/scanner.php` et `parrain/valider.php` sont vides
(seul l'admin peut en ajouter). Les liens du menu vers ces pages existent déjà.

## Décisions prises par le groupe (18/10)

1. **Une seule maquette : celle des équipes 1-2**, appliquée à toutes les pages par `includes/header.php`,
   `includes/footer.php` et `assets/css/style.css` (barre blanche avec logo, bandeau bleu à vagues, cartes arrondies,
   pied de page marine, police Poppins).
2. **On tutoie partout**, administration comprise.
3. **Photo obligatoire pour un DUT1**, à l'inscription comme à la création par l'admin (et l'admin ne peut pas la retirer).
4. **Libellés des types gérés par l'admin** (`admin/libelles.php`) : la base garde `Mame1`, le site affiche « Mame 1 » ou ce que l'admin choisit.
5. Objectif de signatures : 120 (`OBJECTIF_SIGNATURES`, commun).

## Ce qui fonctionne (testé le 18/10 sur la base de test : 120 DUT1, 45 parrains, 2 098 signatures)

- `index.php` → `espace/dut1.php` / `espace/parrain.php` → inscription ou connexion, toutes à la même maquette.
- Inscription DUT1 (photo obligatoire) et parrain (type avec libellés de l'admin, chambre) : validation serveur,
  erreurs sous chaque champ, refus des doublons, CSRF.
- Connexion DUT1 / parrain / admin : 5 essais puis attente, compte suspendu refusé, message après déconnexion ;
  un compte qui ouvre la page d'un autre rôle revient sur son accueil avec un message.
- Menu du haut selon le rôle (DUT1 : Accueil, Registre, Mes signatures, Classement ; parrain : Accueil, Scanner, Mes signatures).
- `dut1/registre.php`, `dut1/signatures.php`, `dut1/classement.php` et leurs API JSON (actualisation toutes les 30 s).
- Administration : tableau de bord, utilisateurs, création (photo DUT1 obligatoire), fiche, modification, suspension,
  suppression, mots de passe, signatures, classement, exports CSV (libellés des types), libellés.
- Le rang est le même partout (`classement_filleuls()`) ; les comptes suspendus n'ont pas de rang.
- Base injoignable : message neutre au lieu d'une trace technique.

## Ce qui reste à faire

### Équipe 3 — espace DUT1 (priorité 1 : bloque le parcours)
- [ ] `dut1/accueil.php` : compteur (`nb_signatures_filleul()`), rang (`rang_filleul()` / `nb_filleuls_actifs()`),
      progression sur `OBJECTIF_SIGNATURES`, bouton « Signer », QR code.
- [ ] `dut1/signer.php` : box de signature sur le téléphone du DUT1 → `verifier_identifiants($pdo, 'parrain', …)`
      puis `enregistrer_signature()` et `MESSAGES_SIGNATURE`.
- [ ] `assets/js/qrcode.js` : QR code de l'adresse `url('parrain/valider.php?dut1=' . $moi['id'])`.
- [ ] `dut1/telecharger.php` : liste des signataires (`signataires_du_filleul()`), en CSV par exemple.
- [ ] `dut1/registre.php` : vérifier `analyser_chambre()` sur les vraies chambres (les données de test `314B`
      donnent « Étage non défini » : numérotation à confirmer), et remplacer l'affichage provisoire si l'équipe le souhaite.

### Équipe 4 — espace parrain (priorité 1 : bloque le parcours)
- [ ] `parrain/accueil.php` : nombre de signatures données (`nb_signatures_parrain()`).
- [ ] `parrain/scanner.php` + `assets/js/scanner.js` : lecture du QR code à la caméra.
- [ ] `parrain/valider.php?dut1=<id>` : confirmation puis `enregistrer_signature()` (formulaire POST + CSRF).
- [ ] `parrain/signatures.php` : DUT1 signés (`filleuls_signes_par()`).
- [ ] `parrain/telecharger.php`.

Pour ces deux équipes : partir du modèle de page du CONTRIBUTING (§3), avec `$bandeau` et les classes communes ;
afficher les types avec `libelle_type()` et tutoyer.

### Commun (avant la mise en ligne)
- [ ] HTTPS, `config/db.php` avec un utilisateur MySQL dédié, `display_errors = Off`.
- [ ] Sur la vraie base, exécuter le bloc `libelle_type` de `sql/signature2027.sql`.

## Git : étapes pour repartir proprement

1. Relire puis fusionner `commun/maquette-unique` dans `main`.
2. Supprimer les branches fusionnées ou abandonnées : `commun/normalisation`, `commun/photo-signataires`,
   `equipe5/signatures-dut1`, `equipe6/bases`, `revert-1-patch-4`, `commun/uniformisation`, puis `commun/maquette-unique`.
3. Protéger `main` (CONTRIBUTING §1, étape 2) : plus aucune fusion sans approbation.
4. Équipes 3 et 4 : `git switch main`, `git pull origin main`, puis une branche `equipe3/accueil`, `equipe4/scanner`…
