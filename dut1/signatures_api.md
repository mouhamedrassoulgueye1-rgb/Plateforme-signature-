# Pages « Classement » et « Mes signatures » (Équipe 5)

| Page | Fichiers |
|---|---|
| Classement de tous les DUT1 | `dut1/classement.php`, `dut1/classement_api.php`, `assets/js/classement.js` |
| Mes signatures (signataires, WhatsApp, nombre, rang) | `dut1/signatures.php`, `dut1/signatures_api.php`, `assets/js/signatures_dut1.js` |
| Commun aux deux | `dut1/signatures_fonctions.php` (données et outils des API), section « Équipe 5 » de `assets/css/style.css` |

## Comment la page reçoit ses données

1. La page PHP lit les données et les met dans l'attribut `data-donnees` de son bloc principal (`#classement` ou `#signatures-dut1`). Ce sont exactement les données que renvoie l'API.
2. Le JavaScript les lit avec `JSON.parse(page.dataset.donnees)` et affiche la page tout de suite.
3. Toutes les 30 secondes, il rappelle l'API (`page.dataset.api`) et redessine seulement ce qui a changé.

Il n'y a pas de script dans le HTML : seulement `<script src="…">` vers `assets/js/`.

L'adresse de l'API vient de `data-api`, qui tient compte de `BASE_URL` (XAMPP ou production). Le cookie de session doit partir avec la requête :

```js
const reponse = await fetch(page.dataset.api, { credentials: 'same-origin', cache: 'no-store' });
```

Les deux API sont en `GET` et sans paramètre : le DUT1 est reconnu par sa session, jamais par l'URL.

## GET dut1/signatures_api.php

Exemple complet : `dut1/signatures_exemple.json`.

| Champ | Type | Contenu |
|---|---|---|
| `nombre` | entier | Signatures reçues par le DUT1 |
| `rang` | entier | Rang parmi les DUT1 actifs. Ex æquo = même rang (10, 8, 8, 5 signatures → rangs 1, 2, 2, 4) |
| `total_dut1` | entier | Nombre de DUT1 actifs, pour « 4e sur 112 » |
| `objectif` | entier | Signatures qui valent 100 % (120) |
| `mis_a_jour` | texte | Heure de la réponse (heure de Dakar), ex. `2026-10-20T14:32:00` |
| `signataires` | tableau | Du plus récent au plus ancien. `[]` si aucune signature |

Chaque signataire :

| Champ | Type | Contenu |
|---|---|---|
| `nom`, `prenom` | texte | |
| `type_parrain` | texte | `Parrain`, `Marraine`, `Mame1`, `Mame2`, `Mame3` ou `Alumni` |
| `chambre` | texte | Telle que saisie à l'inscription, ex. `29G` |
| `photo` | texte ou `null` | Adresse de la photo, utilisable telle quelle dans `src`. `null` : afficher les initiales |
| `whatsapp` | texte ou `null` | Lien `https://wa.me/221…`. `null` : bouton WhatsApp désactivé |
| `date` | texte | Date de la signature, ex. `2026-10-20 14:32` |

Le numéro de téléphone n'est jamais envoyé : seulement le lien WhatsApp.

## GET dut1/classement_api.php

```json
{
  "objectif": 120,
  "mis_a_jour": "2026-10-20T14:32:00",
  "statistiques": { "inscrits": 112, "signatures": 3456, "meilleur": 120, "objectif_atteint": 8 },
  "classement": [
    { "rang": 1, "nom": "Diallo", "prenom": "Ibrahim", "photo": null, "nb_signatures": 120, "moi": false },
    { "rang": 2, "nom": "Fall", "prenom": "Aïssatou", "photo": "/signature2027/uploads/photos/dut1_3f9a1c2b7d4e8a01.jpg", "nb_signatures": 118, "moi": true }
  ]
}
```

| Champ | Contenu |
|---|---|
| `statistiques.inscrits` | Nombre de DUT1 actifs |
| `statistiques.signatures` | Total des signatures reçues par ces DUT1 |
| `statistiques.meilleur` | Plus grand nombre de signatures |
| `statistiques.objectif_atteint` | DUT1 qui ont atteint l'objectif (100 %) |
| `classement` | Tous les DUT1 actifs, du plus signé au moins signé (à égalité : ordre alphabétique) |
| `classement[].moi` | `true` pour le DUT1 connecté (ligne mise en avant, onglet « Mes résultats ») |

Les comptes suspendus par l'admin n'apparaissent pas et ne comptent ni dans le total ni dans les rangs. Aucun identifiant ni téléphone n'est envoyé.

## Erreurs (les deux API)

Toujours du JSON de la forme `{"erreur": "…"}`.

| Code | Quand | Ce que fait le JavaScript |
|---|---|---|
| 401 | Pas connecté en DUT1, ou compte suspendu / supprimé par l'admin | Arrête l'actualisation et affiche « Ta session a expiré » |
| 405 | Méthode autre que GET (en-tête `Allow: GET`) | Erreur de code : utiliser GET |
| 500 | Problème serveur ou base injoignable | Garde l'affichage et réessaie 30 s plus tard |

## Tester en local

La connexion DUT1 (Équipe 2) n'existe pas encore. Pour tester en attendant, créez à la racine un fichier `test_connexion.php`. **Ne le commitez jamais et supprimez-le après le test.**

```php
<?php
require_once __DIR__ . '/includes/init.php';
$req = $pdo->prepare('SELECT * FROM filleul WHERE id_filleul = ?');
$req->execute([(int) ($_GET['id'] ?? 0)]);
$dut1 = $req->fetch() ?: exit('DUT1 introuvable');
$dut1['id'] = (int) $dut1['id_filleul'];
ouvrir_session('dut1', $dut1);
rediriger('dut1/signatures.php');
```

Ouvrez ensuite `http://localhost/signature2027/test_connexion.php?id=1`, puis `dut1/classement.php` avec le lien en haut de la page. Les DUT1, les parrains et les signatures peuvent être créés depuis l'administration (Équipe 6), une fois sa branche fusionnée.
