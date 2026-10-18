<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Connexion parrain, marraine, Mame - Site Signature 2027</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="connexion.css">
</head>
<body data-role="parrain">

<header class="top">
  <svg class="logo" viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="3" aria-hidden="true">
    <circle cx="24" cy="24" r="21"/><path d="M15 33l3-9 14-14 6 6-14 14-9 3z" stroke-linejoin="round"/><path d="M29 13l6 6"/>
  </svg>
  <a class="brand" href="accueil-parrain.html">SITE SIGNATURE <span>2027</span></a>
  <a class="home" href="accueil-parrain.html">Accueil</a>
</header>

<section class="hero">
  <div class="in">
    <span class="school">ESP Dakar</span>
    <h1>Connexion parrain, marraine, Mame</h1>
    <p>Scanne le QR code d'un DUT1 et retrouve tes signatures.</p>
  </div>
</section>

<main>
  <div class="card">
    <h2>Connexion parrain, marraine, Mame</h2>
    <p class="sub">Entre ton nom d'utilisateur et ton mot de passe.</p>

    <form id="form" novalidate>
      <label for="user">Nom d'utilisateur</label>
      <input id="user" name="username" autocomplete="username" autocapitalize="none" spellcheck="false" required>

      <label for="pass">Mot de passe</label>
      <div class="field">
        <input id="pass" name="password" type="password" autocomplete="current-password" required style="padding-right:76px">
        <button type="button" class="eye" id="eye" aria-label="Afficher le mot de passe" aria-pressed="false">Afficher</button>
      </div>

      <p class="msg" id="msg" role="alert" aria-live="polite"></p>

      <button class="btn" id="go" type="submit">
        <span class="spin" aria-hidden="true"></span>
        <span id="goText">Se connecter</span>
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
      </button>
    </form>

  </div>
</main>

<footer>
  <strong>SITE SIGNATURE 2027</strong>
  <p class="slogan">« Un lien aujourd'hui, une force demain ! »</p>
  <small>ESP Dakar &nbsp;|&nbsp; SITE SIGNATURE 2027</small>
</footer>

<script src="connexion-parrain.js"></script>
</body>
</html>