<?php
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/fonctions_inscription.php';

// Déjà connecté : direction la page d'accueil de son rôle
if ($u = utilisateur()) {
    rediriger(ROLES[$u['role']]['accueil']);
}

$erreurs = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $erreurs = traiter_inscription($pdo, 'parrain');
}
$titre_page = 'Inscription parrain, marraine ou Mame';
$bandeau = ['titre' => 'Inscription parrain, marraine ou Mame', 'texte' => 'Un lien aujourd’hui, une force demain !'];
$styles_page = ['assets/css/inscription.css'];
$scripts_page = ['assets/js/inscription.js'];
$classe_main = 'conteneur-plein inscription';
require __DIR__ . '/../includes/header.php';
?>
  <!-- ============ FORMULAIRE ============ -->
  <div class="ins-contenu">
    <div class="carte">
      <div class="carte-embleme" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.6-7 8-7s8 3 8 7"/></svg></div>
      <h2>Formulaire parrain, marraine ou Mame</h2>
      <p class="intro">Crée ton compte pour signer pour les DUT1. Les champs marqués d'une étoile sont obligatoires.</p>

      <form id="form-inscription" method="post" enctype="multipart/form-data" novalidate>
          <?= champ_csrf() ?>

          <div class="grille">

            <div class="champ">
              <label for="nom">Nom <span class="etoile">*</span></label>
              <input type="text" id="nom" name="nom" value="<?= valeur_saisie('nom') ?>" required data-regle="texte" data-max="50"
                     placeholder="Entre ton nom" autocomplete="family-name" aria-describedby="err-nom">
              <p class="message-erreur" id="err-nom" aria-live="polite"><?= erreur_champ($erreurs, 'nom') ?></p>
            </div>

            <div class="champ">
              <label for="prenom">Prénom <span class="etoile">*</span></label>
              <input type="text" id="prenom" name="prenom" value="<?= valeur_saisie('prenom') ?>" required data-regle="texte" data-max="80"
                     placeholder="Entre ton prénom" autocomplete="given-name" aria-describedby="err-prenom">
              <p class="message-erreur" id="err-prenom" aria-live="polite"><?= erreur_champ($erreurs, 'prenom') ?></p>
            </div>

            <div class="champ">
              <label for="type_parrain">Tu es <span class="etoile">*</span></label>
              <select id="type_parrain" name="type_parrain" required data-regle="liste" aria-describedby="err-type_parrain">
                <option value="">Choisis ton type</option>
                <?php foreach (TYPES_PARRAIN as $t): ?>
                  <option value="<?= e($t) ?>" <?= valeur_saisie('type_parrain') === e($t) ? 'selected' : '' ?>><?= e(libelle_type($t)) ?></option>
                <?php endforeach; ?>
              </select>
              <p class="message-erreur" id="err-type_parrain" aria-live="polite"><?= erreur_champ($erreurs, 'type_parrain') ?></p>
            </div>


            <div class="champ">
              <label for="chambre">Chambre <span class="etoile">*</span></label>
              <input type="text" id="chambre" name="chambre" value="<?= valeur_saisie('chambre') ?>" required data-regle="texte" data-max="30"
                     placeholder="Numéro de ta chambre" aria-describedby="err-chambre">
              <p class="message-erreur" id="err-chambre" aria-live="polite"><?= erreur_champ($erreurs, 'chambre') ?></p>
            </div>

            <div class="champ">
              <label for="telephone">Téléphone <span class="etoile">*</span></label>
              <input type="tel" id="telephone" name="telephone" value="<?= valeur_saisie('telephone') ?>" required data-regle="telephone"
                     placeholder="77 123 45 67" autocomplete="tel" aria-describedby="err-telephone">
              <p class="message-erreur" id="err-telephone" aria-live="polite"><?= erreur_champ($erreurs, 'telephone') ?></p>
            </div>

            <div class="champ plein">
              <label for="nom_utilisateur">Nom d'utilisateur <span class="etoile">*</span></label>
              <input type="text" id="nom_utilisateur" name="nom_utilisateur" value="<?= valeur_saisie('nom_utilisateur') ?>" required data-regle="utilisateur" data-max="30"
                     placeholder="Choisis un nom d'utilisateur" autocomplete="username" aria-describedby="err-nom_utilisateur">
              <p class="message-erreur" id="err-nom_utilisateur" aria-live="polite"><?= erreur_champ($erreurs, 'nom_utilisateur') ?></p>
            </div>

            <div class="champ">
              <label for="mot_de_passe">Mot de passe <span class="etoile">*</span></label>
              <div class="champ-mdp">
                <input type="password" id="mot_de_passe" name="mot_de_passe" required data-regle="mdp" data-max="72"
                       placeholder="8 caractères minimum" autocomplete="new-password" aria-describedby="err-mot_de_passe">
                <button type="button" class="oeil" data-cible="mot_de_passe" aria-label="Afficher le mot de passe">Afficher</button>
              </div>
              <p class="message-erreur" id="err-mot_de_passe" aria-live="polite"><?= erreur_champ($erreurs, 'mot_de_passe') ?></p>
            </div>

            <div class="champ">
              <label for="confirmation_mot_de_passe">Confirmation <span class="etoile">*</span></label>
              <div class="champ-mdp">
                <input type="password" id="confirmation_mot_de_passe" name="confirmation_mot_de_passe" required data-regle="confirmation" data-max="72"
                       placeholder="Retape ton mot de passe" autocomplete="new-password" aria-describedby="err-confirmation_mot_de_passe">
                <button type="button" class="oeil" data-cible="confirmation_mot_de_passe" aria-label="Afficher le mot de passe">Afficher</button>
              </div>
              <p class="message-erreur" id="err-confirmation_mot_de_passe" aria-live="polite"><?= erreur_champ($erreurs, 'confirmation_mot_de_passe') ?></p>
            </div>

            <div class="champ plein">
              <span class="etiquette">Photo <span class="facultatif">(facultative)</span></span>
              <div class="zone-photo">
                <div class="apercu" id="apercu">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-7 8-7s8 3 8 7"/></svg>
                </div>
                <div>
                  <label class="btn-photo">
                    Choisir une photo
                    <input type="file" id="photo" name="photo" accept="image/jpeg,image/png,image/webp" data-regle="photo" aria-describedby="err-photo">
                  </label>
                  <p class="aide-photo">Format JPG, PNG ou WebP, 2 Mo maximum.</p>
                </div>
              </div>
              <p class="message-erreur" id="err-photo" aria-live="polite"><?= erreur_champ($erreurs, 'photo') ?></p>
            </div>

          </div>

          <button type="submit" class="btn">Créer mon compte</button>
          <div class="alerte<?= $erreurs ? ' echec visible' : '' ?>" id="alerte" role="status"><?= $erreurs ? 'Certains champs sont à corriger avant de continuer.' : '' ?></div>
        </form>

      <p class="lien-bas">Tu as déjà un compte ? <a href="<?= e(url('connexion/parrain.php')) ?>">Se connecter</a></p>
    </div>
  </div>

  <section class="ins-valeurs" aria-label="Les valeurs de Site Signature">
    <article class="valeur"><span class="valeur-icone" aria-hidden="true">♧</span><h3>Accompagnement</h3><p>Des parrains, marraines et Mames à ton écoute</p></article>
    <article class="valeur"><span class="valeur-icone" aria-hidden="true">◎</span><h3>Solidarité</h3><p>Une communauté unie et engagée</p></article>
    <article class="valeur"><span class="valeur-icone" aria-hidden="true">☆</span><h3>Réussite</h3><p>Ensemble vers de grands objectifs</p></article>
  </section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
