<?php
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/fonctions_connexion.php';

// Déjà connecté : direction la page d'accueil de son rôle
if ($u = utilisateur()) {
    rediriger(ROLES[$u['role']]['accueil']);
}

$erreur = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $erreur = traiter_connexion($pdo, 'dut1');
}
$messages = lire_flash();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Connexion DUT1 - Site Signature 2027</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= e(url('assets/css/connexion.css')) ?>">
</head>
<body data-role="dut1">

<header class="top">
  <svg class="logo" viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="3" aria-hidden="true">
    <circle cx="24" cy="24" r="21"/><path d="M15 33l3-9 14-14 6 6-14 14-9 3z" stroke-linejoin="round"/><path d="M29 13l6 6"/>
  </svg>
  <a class="brand" href="<?= e(url('espace/dut1.php')) ?>">SITE SIGNATURE <span>2027</span></a>
  <a class="home" href="<?= e(url('espace/dut1.php')) ?>">Accueil</a>
</header>

<section class="hero">
  <div class="in">
    <span class="school">ESP Dakar</span>
    <h1>Connexion DUT1</h1>
    <p>Retrouve ton compteur, ton code QR et ta liste de signatures.</p>
  </div>
</section>

<main>
  <div class="card">
    <h2>Connexion DUT1</h2>
    <p class="sub">Entre ton nom d'utilisateur et ton mot de passe.</p>

    <?php foreach ($messages as $m): ?>
      <p class="msg<?= $m['type'] === 'erreur' ? '' : ' ok' ?>"><?= e($m['message']) ?></p>
    <?php endforeach; ?>

    <form id="form" method="post" novalidate>
      <?= champ_csrf() ?>
      <label for="nom_utilisateur">Nom d'utilisateur</label>
      <input id="nom_utilisateur" name="nom_utilisateur" autocomplete="username" autocapitalize="none" spellcheck="false" required maxlength="30"
             value="<?= e($_POST['nom_utilisateur'] ?? '') ?>" aria-invalid="<?= $erreur ? 'true' : 'false' ?>">

      <label for="mot_de_passe">Mot de passe</label>
      <div class="field">
        <input id="mot_de_passe" name="mot_de_passe" type="password" autocomplete="current-password" required class="champ-avec-oeil"
               aria-invalid="<?= $erreur ? 'true' : 'false' ?>">
        <button type="button" class="eye" id="eye" aria-label="Afficher le mot de passe" aria-pressed="false">Afficher</button>
      </div>

      <p class="msg" id="msg" role="alert" aria-live="polite"><?= e($erreur) ?></p>

      <button class="btn" id="go" type="submit">
        <span class="spin" aria-hidden="true"></span>
        <span id="goText">Se connecter</span>
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
      </button>
    </form>

    <p class="links">Pas encore de compte ? <a href="<?= e(url('inscription/dut1.php')) ?>">Créer un compte</a></p>
  </div>
</main>

<footer>
  <strong>SITE SIGNATURE 2027</strong>
  <p class="slogan">« Un lien aujourd'hui, une force demain ! »</p>
  <small>ESP Dakar &nbsp;|&nbsp; SITE SIGNATURE 2027</small>
</footer>

<script src="<?= e(url('assets/js/connexion.js')) ?>"></script>
</body>
</html>
