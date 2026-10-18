<?php
// Équipes 1-2 : page d'accueil publique de l'espace parrains, marraines et Mames (créer un compte ou se connecter).
require_once __DIR__ . '/../includes/init.php';

// Déjà connecté : direction la page d'accueil de son rôle
if ($u = utilisateur()) {
    rediriger(ROLES[$u['role']]['accueil']);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#052a63">
<title>Espace parrain, marraine et Mame - Site Signature 2027 - ESP Dakar</title>
<meta name="description" content="Espace parrain, marraine et Mame : scanne le code QR de tes filleuls et signe pour eux.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<link rel="stylesheet" href="<?= e(url('assets/css/accueil.css')) ?>">
</head>

<body data-role="parrain">

<!-- Symboles réutilisables (logo + motif circuit) -->
<svg width="0" height="0" style="position:absolute" aria-hidden="true">
  <symbol id="logo" viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round">
    <circle cx="24" cy="24" r="21"/><path d="M14 35l3.4-10L33 9.5 38.5 15 23 30.6 14 35z"/><path d="M29.5 13l5.5 5.5M17.5 25l5.5 5.5"/>
  </symbol>
  <symbol id="circuit" viewBox="0 0 180 130" fill="none" stroke="currentColor" stroke-width="1.5">
    <path d="M0 40h50l22 -22h40M0 70h38l26 26h70M0 105h60M72 18v-18M112 18h30"/>
    <g fill="currentColor" stroke="none"><circle cx="112" cy="18" r="3.5"/><circle cx="134" cy="96" r="3.5"/><circle cx="60" cy="105" r="3.5"/><circle cx="142" cy="18" r="3.5"/></g>
  </symbol>
</svg>

<!-- ============ EN-TETE ============ -->
<header class="top" id="top">
  <div class="wrap">
    <a class="brand" href="#top" aria-label="Site Signature 2027 - accueil">
      <svg class="logo" aria-hidden="true"><use href="#logo"/></svg>
      <b>SITE SIGNATURE <span>2027</span></b>
    </a>
    <nav class="nav" aria-label="Navigation principale">
      <a class="home" href="#top" aria-current="page">
        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 3 2 12h3v8h5v-5h4v5h5v-8h3L12 3z"/></svg>Accueil
      </a>
      <button class="burger" id="burger" aria-label="Ouvrir le menu" aria-expanded="false" aria-controls="menu">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
      </button>
    </nav>
  </div>
  <div class="menu" id="menu" hidden>
    <a href="#top">Accueil</a>
    <a id="mSignup" href="<?= e(url('inscription/parrain.php')) ?>">Créer un compte<small>Nouveau parrain, marraine ou Mame</small></a>
    <a id="mLogin" href="<?= e(url('connexion/parrain.php')) ?>">Se connecter<small>Scanner et signer</small></a>

  </div>
</header>

<main>
<!-- ============ HERO ============ -->
<section class="hero">
  <svg class="circuit-tl" aria-hidden="true"><use href="#circuit"/></svg>
  <div class="photo" role="img" aria-label="Entrée de l'École Supérieure Polytechnique de Dakar"></div>
  <div class="wrap">
    <div class="txt">
      <span class="role-tag"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 20l4-1 11-11-3-3L5 16l-1 4zM14 6l3 3"/></svg>Espace parrain, marraine et Mame</span>
      <div class="school">ESP <span>DAKAR</span></div>
      <h1>SITE SIGNATURE <span>2027</span></h1>
      <p class="slogan">Un lien aujourd'hui, une force demain !</p>
      <p class="desc">Soutiens les DUT1 de l'ESP : scanne leur code QR et signe pour eux en quelques secondes.</p>
    </div>
  </div>
</section>

<!-- ============ BOUTONS ============ -->
<div class="cta-wrap">
  <div class="cta-row">
    <a class="cta signup" id="goSignup" href="<?= e(url('inscription/parrain.php')) ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="9" cy="8" r="4"/><path d="M2 21c0-4 3.2-6 7-6s7 2 7 6M19 8v6M16 11h6"/></svg>
      Créer un compte
    </a>
    <a class="cta" id="goLogin" href="<?= e(url('connexion/parrain.php')) ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 4h4a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-4M3 12h12M10 7l5 5-5 5"/></svg>
      Se connecter
    </a>
  </div>
</div>

<!-- ============ VALEURS ============ -->
<section class="values" aria-label="Nos valeurs">
  <div class="wrap"><div class="grid">
    <article class="val">
      <div class="ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="3"/><circle cx="5" cy="10" r="2.2"/><circle cx="19" cy="10" r="2.2"/><path d="M6.5 19c0-3 2.5-5 5.5-5s5.500 2 5.500 5M1.500 17c0-2 1.500-3.500 3.500-3.500M22.500 17c0-2-1.500-3.500-3.500-3.500"/></svg></div>
      <h3>Solidarité</h3><p>Unis pour accompagner les DUT1 vers la réussite.</p>
    </article>
    <article class="val">
      <div class="ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 4 2 9l10 5 10-5-10-5z"/><path d="M6 11.500V16c0 1.500 2.700 3 6 3s6-1.500 6-3v-4.500M22 9v6"/></svg></div>
      <h3>Engagement</h3><p>Chaque signature compte !</p>
    </article>
    <article class="val">
      <div class="ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 17l6-6 4 4 8-8"/><path d="M15 7h6v6"/></svg></div>
      <h3>Réussite</h3><p>Ensemble pour un avenir plus grand.</p>
    </article>
  </div></div>
</section>
</main>

<!-- ============ PIED DE PAGE ============ -->
<footer class="foot">
  <svg class="circuit-bl" aria-hidden="true"><use href="#circuit"/></svg>
  <svg class="circuit-br" aria-hidden="true"><use href="#circuit"/></svg>
  <svg class="logo" aria-hidden="true"><use href="#logo"/></svg>
  <div class="ttl"><b>SITE SIGNATURE <span>2027</span></b></div>
  <p class="sl">« Un lien aujourd'hui, une force demain ! »</p>
  <div class="meta">
    <span class="e"><svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2a7 7 0 0 0-7 7c0 5 7 13 7 13s7-8 7-13a7 7 0 0 0-7-7zm0 9.500A2.500 2.500 0 1 1 12 6.500a2.500 2.500 0 0 1 0 5z"/></svg> ESP Dakar</span>
    <i></i><span>SITE SIGNATURE 2027</span>
  </div>
</footer>

<script src="<?= e(url('assets/js/accueil.js')) ?>"></script>
</body>
</html>
