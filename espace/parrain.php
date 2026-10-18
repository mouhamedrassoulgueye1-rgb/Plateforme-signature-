<?php
// Équipes 1-2 : page d'accueil publique de l'espace parrains, marraines et Mames (créer un compte ou se connecter).
require_once __DIR__ . '/../includes/init.php';

// Déjà connecté : direction la page d'accueil de son rôle
if ($u = utilisateur()) {
    rediriger(ROLES[$u['role']]['accueil']);
}
$titre_page = 'Espace parrain, marraine et Mame';
$styles_page = ['assets/css/espace.css'];
$scripts_page = ['assets/js/espace.js'];
$classe_main = 'conteneur-plein espace';
require __DIR__ . '/../includes/header.php';
?>
<!-- ============ HERO ============ -->
<section class="espace-hero">
  <svg class="espace-circuit" aria-hidden="true"><use href="#circuit"/></svg>
  <div class="espace-photo" role="img" aria-label="Entrée de l'École Supérieure Polytechnique de Dakar"></div>
  <div class="espace-largeur">
    <div class="espace-texte">
      <span class="espace-role"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 20l4-1 11-11-3-3L5 16l-1 4zM14 6l3 3"/></svg>Espace parrain, marraine et Mame</span>
      <div class="espace-ecole">ESP <span>DAKAR</span></div>
      <h1>SITE SIGNATURE <span>2027</span></h1>
      <p class="espace-slogan">Un lien aujourd'hui, une force demain !</p>
      <p class="espace-desc">Soutiens les DUT1 de l'ESP : scanne leur code QR et signe pour eux en quelques secondes.</p>
    </div>
  </div>
</section>

<!-- ============ BOUTONS ============ -->
<div class="espace-actions">
  <div class="espace-actions-ligne">
    <a class="espace-action espace-action-inscription" href="<?= e(url('inscription/parrain.php')) ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="9" cy="8" r="4"/><path d="M2 21c0-4 3.2-6 7-6s7 2 7 6M19 8v6M16 11h6"/></svg>
      Créer un compte
    </a>
    <a class="espace-action" href="<?= e(url('connexion/parrain.php')) ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 4h4a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-4M3 12h12M10 7l5 5-5 5"/></svg>
      Se connecter
    </a>
  </div>
</div>

<!-- ============ VALEURS ============ -->
<section class="espace-valeurs" aria-label="Nos valeurs">
  <div class="espace-largeur"><div class="espace-grille">
    <article class="espace-valeur">
      <div class="espace-icone"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="3"/><circle cx="5" cy="10" r="2.2"/><circle cx="19" cy="10" r="2.2"/><path d="M6.5 19c0-3 2.5-5 5.5-5s5.500 2 5.500 5M1.500 17c0-2 1.500-3.500 3.500-3.500M22.500 17c0-2-1.500-3.500-3.500-3.500"/></svg></div>
      <h3>Solidarité</h3><p>Unis pour accompagner les DUT1 vers la réussite.</p>
    </article>
    <article class="espace-valeur">
      <div class="espace-icone"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 4 2 9l10 5 10-5-10-5z"/><path d="M6 11.500V16c0 1.500 2.700 3 6 3s6-1.500 6-3v-4.500M22 9v6"/></svg></div>
      <h3>Engagement</h3><p>Chaque signature compte !</p>
    </article>
    <article class="espace-valeur">
      <div class="espace-icone"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 17l6-6 4 4 8-8"/><path d="M15 7h6v6"/></svg></div>
      <h3>Réussite</h3><p>Ensemble pour un avenir plus grand.</p>
    </article>
  </div></div>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
