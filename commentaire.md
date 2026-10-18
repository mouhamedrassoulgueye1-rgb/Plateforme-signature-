# Revue du code — Signature 2027

Revue faite le 18/10/2026 sur toutes les branches du dépôt GitHub (`main`, `commun/normalisation`,
`commun/photo-signataires`, `equipe1-2/connexion`, `equipe5/signatures-dut1`, `equipe6/bases`, `revert-1-patch-4`).
Les corrections sont sur la branche **`commun/uniformisation`** (fusionnée dans `main`), puis **`commun/maquette-unique`**
(maquette unique, tutoiement, fin du travail des équipes 5 et 6 ; voir [evolution.md](evolution.md) pour l'état du travail).

Légende : 🔴 bloquant (le site plante ou ne fait pas ce qu'il doit) · 🟠 nuit à la cohésion · 🟡 mauvaise pratique · ✅ corrigé dans `commun/uniformisation`.

---

## 1. Équipes 1-2 — connexion, inscription, pages d'accueil (PR #4, commit `f49d41c`)

Le travail visuel est soigné (maquettes, accessibilité, vérification en direct des champs), mais **rien ne fonctionnait
avec le reste du site** : le code a été écrit comme s'il n'existait ni `includes/`, ni `CONTRIBUTING.md`.

### Erreurs bloquantes

| | Problème | Conséquence |
|---|---|---|
| 🔴 ✅ | Les scripts PHP font `require config/db.php // doit définir $pdo`, mais `config/db.php` ne définit que des constantes : `$pdo` n'existe pas. | *Fatal error* à chaque connexion et à chaque inscription. |
| 🔴 ✅ | Session maison : `$_SESSION['utilisateur_id']`, `$_SESSION['role']`… au lieu de `$_SESSION['utilisateur']` (format de `includes/auth.php`). | `exiger_role()` ne reconnaît jamais l'utilisateur : toutes les pages protégées des autres équipes renvoient vers la connexion, en boucle. |
| 🔴 ✅ | `DEMO_MODE = true` (connexion) et `MODE_DEMO = true` (inscription). | La connexion accepte **n'importe quel identifiant** et l'inscription n'envoie rien au serveur. Dangereux si mis en ligne tel quel. |
| 🔴 ✅ | Les formulaires d'inscription n'ont pas de champ `role`, que `inscription.php` exige. | Toute inscription est refusée (`role_invalide`). |
| 🔴 ✅ | Le PHP lit `$_POST['confirmation']`, le formulaire envoie `confirmation_mot_de_passe`. | Toujours « mots de passe différents ». |
| 🔴 ✅ | INSERT parrain avec une colonne `niveau` qui n'existe pas, sans la colonne `chambre` (`NOT NULL`). | Aucun parrain ne peut s'inscrire (erreur SQL). |
| 🔴 ✅ | `TYPES_PARRAIN` redéfini à `['Parrain', 'Marraine', 'Mame']` alors que la constante commune existe ; le `<select>` envoie `Mame 1` (avec espace) pour **les trois** options Mame. | Redéclaration = *Fatal error* avec `init.php` ; aucune Mame ne peut s'inscrire ; Mame 2 et 3 impossibles à distinguer ; la base attend `Mame1`, `Mame2`, `Mame3`, `Alumni`. |
| 🔴 ✅ | Les codes d'erreur du PHP (`champ_manquant`, `nom_invalide`…) ne correspondent pas à ceux du JS (`champ_invalide`, `mdp_different`…) et le bloc `else if (parametres.has("erreur")) { }` est vide. | L'utilisateur ne voit jamais pourquoi son inscription a échoué. |

### Problèmes de cohésion

- 🟠 ✅ **13 fichiers à la racine** (`connexion-dut1.html`, `inscription.css`, `esp.jpg`…) alors que le README fixe
  `connexion/`, `inscription/`, `assets/css/`, `assets/js/`. Les pages vides prévues (`connexion/dut1.php`…) ont été laissées vides à côté.
- 🟠 ✅ **Formats de données différents des autres équipes** : téléphone stocké `+221…` (convention : `221…`), le JS refuse
  `77 123 45 67` ; nom d'utilisateur avec majuscules et `-` sans mise en minuscules, alors que la connexion admin et
  `verifier_identifiants()` cherchent en minuscules (un compte `Awa.Ndiaye` n'aurait jamais pu se connecter).
- 🟠 ✅ **Connexion administrateur en double** (`connexion-admin.html`, `login-admin.php`) alors que `admin/connexion.php`
  existe (Équipe 6). Deux portes d'entrée avec des règles différentes (60 s de blocage contre 5 min).
- 🟠 ✅ Réécriture de fonctions communes : session, recherche de doublon de nom d'utilisateur, vérification de photo,
  réponse JSON… au lieu d'appeler `includes/fonctions.php` et `includes/auth.php` (CONTRIBUTING §5 : « on les appelle, on ne les réécrit pas »).
- 🟠 ✅ Les CSS des équipes 1-2 redéfinissent des sélecteurs globaux (`.btn`, `.card`, `input`, `label`, `footer`) au lieu de
  préfixer leurs classes. Ça marche tant que ces pages ne chargent pas `style.css`, mais **le jour où on les met sous
  `header.php`, tout se mélange**. Corrigé : leur maquette est devenue la maquette commune (`style.css`), le reste est préfixé (`espace-`) ou limité à `.inscription`.
- 🟠 ✅ Tutoiement sur les pages des équipes 1-2 et 5, vouvoiement dans le code commun et l'admin. Décision : on tutoie partout.

### Mauvaises pratiques

- 🟡 ✅ Trois fichiers JS de connexion identiques à 3 lignes près, trois pages HTML quasi identiques (un seul `connexion.js` maintenant).
- 🟡 ✅ Liens écrits en dur (`href="connexion-dut1.html"`, `ROUTES` dans `accueil.js`) : cassés dès que l'adresse change. Ils passent maintenant par `url()`.
- 🟡 ✅ Script et style dans le HTML (`<script>document.documentElement.classList.add('js')</script>`, `style="padding-right:76px"`), contraire au CONTRIBUTING.
- 🟡 ✅ Fautes : `data-connexion="connection-parrain.html"`, `<div class="menu">` jamais fermé dans l'accueil DUT1.
- 🟡 ✅ Noms de variables JS en anglais (`say`, `invalid`, `username`, `show`) alors que la convention est le français.
- 🟡 Un seul commit de 2 448 lignes, sans préfixe `[Équipe N]`, branche `equipe1-2/connexion` qui mélange deux équipes :
  impossible de relire ou d'annuler une partie seulement.
- 🟡 Le commentaire de `inscription.css` dit « ce fichier se charge APRÈS style.css », ce qui était faux.

---

## 2. Équipe 3 — registre (PR #1, commit `b560854`)

| | Problème | Conséquence |
|---|---|---|
| 🔴 ✅ | Redéfinit `e()`. | *Fatal error « Cannot redeclare e() »* dès que la page inclut `init.php`. |
| 🔴 ✅ | Connexion PDO écrite en dur (`root`, mot de passe vide) au lieu de `config/db.php`. | Ne marche que sur la machine de l'auteur ; mot de passe en clair dans le dépôt en production. |
| 🔴 ✅ | Session maison : `$_SESSION['role'] === 'filleul'`, `$_SESSION['id']`, redirection vers `connexion_dut1.php` (n'existe pas). | Personne ne peut ouvrir la page. |
| 🔴 ✅ | Le HTML est lu dans `dut1/equipe3.html` et le script dans `equipe3.js`, **jamais envoyés sur GitHub**. | Page blanche. |
| 🟠 ✅ | Fichier commité en fins de ligne Windows (CRLF) malgré `.gitattributes`. | Faux conflits sur tout le fichier. |

- 🟡 Moteur de gabarits maison (expressions régulières sur des commentaires HTML) : beaucoup de code pour ce que le
  modèle de page du CONTRIBUTING fait en 3 lignes. Remplacé par un affichage simple ; la logique des pavillons/étages
  (`analyser_chambre()`, `construire_arbre()`) est **conservée telle quelle**, elle est bien faite.
- 🟡 Message de commit « la partie du registre negro » : non descriptif, sans préfixe, et avec un terme qui peut être
  perçu comme offensant dans un dépôt partagé. À éviter.
- 🟡 La PR a été fusionnée sans relecture visible, puis une branche `revert-1-patch-4` a été ouverte pour l'annuler et
  laissée en plan. Le groupe doit trancher : la supprimer (le registre est maintenant corrigé) ou l'assumer.

---

## 3. Équipe 5 — Mes signatures, Classement (branche `equipe5/signatures-dut1`, non fusionnée)

**Le meilleur exemple à suivre** : `init.php`, `exiger_role()`, fonctions communes appelées et non réécrites, `e()`
partout, JS échappé (`esc()`), API documentée (`signatures_api.md`) avec un exemple JSON, commits petits et bien nommés.

- 🟠 ✅ Dépendait de `commun/photo-signataires` (ajout de `p.photo` dans `signataires_du_filleul()`), elle aussi non
  fusionnée : les photos étaient toujours `null`. Les deux branches sont fusionnées dans `commun/uniformisation`.
- 🟠 ✅ Conflit sur `assets/css/style.css` avec la section de l'Équipe 6 (les deux ajoutaient à la fin du fichier). Résolu en gardant les deux.
- 🟠 ✅ `OBJECTIF_SIGNATURES = 120` est une règle métier décidée par une seule équipe. Si l'accueil DUT1 (Équipe 3)
  affiche une progression, il faudra la même valeur. Déplacée dans `includes/signatures.php`.
- 🟡 ✅ Colonne « Promotion » qui affiche toujours 2027 (la base n'a pas cette colonne) : inutile, à retirer ou à remplacer.
- 🟡 Variables JS en anglais (`rank`, `name`, `state`, `PER_PAGE`).
- 🟡 ✅ `signatures_api.md` recommande un `test_connexion.php` qui ouvre une session sans mot de passe : il n'a plus
  lieu d'être maintenant que la connexion fonctionne, et il serait dangereux s'il était commité par erreur.

---

## 4. Équipe 6 — administration (PR #3, fusionnée)

Travail propre et conforme : CSRF partout, requêtes préparées, `exiger_role('admin')`, double confirmation avant
suppression, suspension plutôt que suppression, réutilisation de `enregistrer_signature()` pour l'ajout manuel.

- 🔴 ✅ **Travail local non envoyé** : sur le poste où la revue a été faite, un *stash* (`stash@{0}`, barre latérale
  `admin_debut()` / `admin_fin()` / `admin_icone()`, en-tête modifié) et quatre fichiers non suivis
  (`admin/classement.php`, `admin/telechargements.php`, `admin/deconnexion.php`, `sql/donnees_test.sql`).
  `classement.php` et `telechargements.php` appellent des fonctions qui n'existent que dans le stash :
  **les pousser seuls provoquerait une erreur fatale**. Intégrés ensemble dans `commun/maquette-unique`.
- 🟠 ✅ Le stash modifie `includes/header.php` (fichier Commun) : à passer par une PR Commun.
- 🟠 ✅ `admin/deconnexion.php` (non suivi) fait `session_start()` après `fermer_session()` : avec la correction commune
  (§5), cette ligne doit être retirée. Il fait de toute façon doublon avec `connexion/deconnexion.php`, qui gère aussi l'admin.
- 🟠 ✅ Le rang est calculé à **cinq endroits** de cinq façons : `rang_filleul()` (SQL, DUT1 actifs), `lire_classement()`
  (Équipe 5, actifs), `admin/index.php`, `admin/classement.php`, `admin/telechargements.php` (tous les DUT1, suspendus compris).
  L'admin et le DUT1 ne voient donc pas le même rang. → une seule fonction commune `classement_filleuls()`, utilisée partout.
- 🟡 `header('Location: ' . admin_url(…)); exit;` répété au lieu d'une fonction ; mineur.
- 🟡 `donnees_test.sql` commence par `DELETE FROM` sur toutes les tables : à ne jamais exécuter sur la vraie base.

---

## 5. Code commun

- 🔴 ✅ `fermer_session()` supprimait le cookie de session, puis les pages appelaient `flash()` : le message
  « Vous êtes déconnecté » ou « Votre compte n'est plus actif » était **toujours perdu**. Corrigé : l'identifiant de
  session est changé et la session reste ouverte, vide.
- ✅ Ajout de `lire_flash()` pour les pages qui ont leur propre maquette (connexion, inscription).
- 🔴 ✅ `verifier_csrf()` acceptait un formulaire **sans jeton** quand la session n'en avait pas encore
  (`hash_equals('', '')` est vrai) : un formulaire envoyé depuis un autre site passait. Le jeton vide est maintenant refusé.
- 🟠 ✅ Un parrain connecté qui ouvre une page DUT1 reçoit « Connectez-vous pour accéder à cette page » alors qu'il est
  connecté : message trompeur. Il revient maintenant sur son accueil avec « Cette page n'est pas accessible avec ton compte ».
- 🟡 ✅ `init.php` ne capture pas l'erreur si MySQL est arrêté : avec `display_errors` activé, la trace affiche le DSN
  et l'utilisateur MySQL. Un `try/catch` avec un message neutre et `error_log()` suffirait.
- 🟡 Les limites de tentatives de connexion (équipes 2 et 6) sont stockées dans la session : il suffit d'effacer ses
  cookies pour les contourner. Suffisant pour un projet d'école, mais à savoir (vraie protection : table des tentatives par IP).

---

## 6. Remarques générales et critiques

1. **Le CONTRIBUTING n'a pas été lu par toutes les équipes.** Les équipes 5 et 6 l'ont suivi et leur code s'emboîte
   sans effort ; les équipes 1-2 et 3 ont recréé leur propre « mini-site » (connexion à la base, session, `e()`), ce qui
   a produit toutes les erreurs bloquantes ci-dessus. Le modèle de page du §3 doit être le point de départ de **chaque** fichier.
2. **Les relectures ne sont pas faites.** Les PR #1 et #4 ont été fusionnées alors qu'elles plantaient au premier
   chargement. Règle à appliquer : la personne qui relit ouvre la page en local avant d'approuver. Protéger `main`
   (CONTRIBUTING §1, étape 2) empêcherait aussi les fusions sans approbation.
3. **Tester avec de vraies données.** Les modes « démo » donnent l'impression que tout marche alors que le serveur n'a
   jamais été appelé. `sql/donnees_test.sql` (Équipe 6) devrait être commité pour que tout le monde teste sur la même base.
4. ✅ **Deux identités visuelles.** Les équipes 1-2 (Poppins, bleu nuit, bandeaux) et 5 ont une maquette riche ; le reste
   du site utilise `style.css` (sobre). Le groupe doit choisir : soit adopter la maquette des équipes 1-2 dans
   `style.css` + `header.php` pour tout le monde, soit ramener les pages publiques à `style.css`. Les deux en même temps
   donnent un site qui change d'apparence d'une page à l'autre. **Décidé : maquette des équipes 1-2 partout.**
5. **Une branche = une équipe = un sujet**, et des commits courts : `equipe1-2/connexion` (un commit de 2 448 lignes)
   et `patch-4` sur un fork rendent la relecture impossible.
6. **Branches à nettoyer** : `commun/normalisation` et `equipe6/bases` (déjà fusionnées), `revert-1-patch-4`
   (abandonnée), puis `commun/photo-signataires` et `equipe5/signatures-dut1` une fois `commun/uniformisation` fusionnée.
7. Le langage (tu/vous : ✅ tutoiement), le format des dates (`d/m/Y H:i` en admin, `2026-10-20 14:32` côté Équipe 5) et les libellés
   des types (✅ `libelle_type()`, réglés par l'admin) gagneraient à être fixés une fois pour toutes dans le CONTRIBUTING. Reste : le format des dates.
