## Ce que fait cette pull request

<!-- Une phrase. Ex. : « Page d'inscription DUT1 avec validation et photo. » -->

**Équipe :** <!-- Équipe 1 à 6, ou Commun -->

## Vérifications avant de demander la relecture

- [ ] J'ai fait `git pull origin main` et ma branche est à jour.
- [ ] Je n'ai modifié que les fichiers de mon équipe (ou j'ai prévenu le groupe pour un fichier Commun).
- [ ] La page commence par `require_once __DIR__ . '/../includes/init.php';`.
- [ ] Toutes les requêtes SQL sont préparées (`prepare` + `execute`), aucune variable dans le texte SQL.
- [ ] Tout ce qui est affiché passe par `e()`.
- [ ] Chaque formulaire POST contient `<?= champ_csrf() ?>` et le traitement appelle `verifier_csrf()`.
- [ ] Les noms des champs de formulaire sont ceux des colonnes de la base.
- [ ] J'ai testé la page en local (ordinateur et largeur téléphone).
- [ ] Aucun `config/db.php`, aucune photo de `uploads/photos/` dans le commit.
