<?php
/**
 * En-tête commun. Dans une page :
 *   $titre_page = 'Mes signatures';
 *   require __DIR__ . '/../includes/header.php';
 */
$u = utilisateur();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e(($titre_page ?? 'Accueil') . ' · Signature 2027') ?></title>
    <link rel="stylesheet" href="<?= e(url('assets/css/style.css')) ?>">
</head>
<body>
<header class="entete">
    <a class="entete-logo" href="<?= e(url($u ? ROLES[$u['role']]['accueil'] : 'index.php')) ?>">
        <svg width="34" height="34" viewBox="0 0 34 34" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="17" cy="17" r="15"/><path d="M10 24h4l10-10a2.8 2.8 0 0 0-4-4L10 20z"/></svg>
        Site Signature 2027
    </a>
    <?php if ($u): ?>
        <nav class="entete-nav">
            <span class="entete-utilisateur">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>
                <?= e($u['role'] === 'admin' ? 'Admin' : $u['prenom']) ?>
            </span>
            <a href="<?= e(url($u['role'] === 'admin' ? 'admin/deconnexion.php' : 'connexion/deconnexion.php')) ?>">Déconnexion</a>
        </nav>
    <?php endif; ?>
</header>
<main class="conteneur">
<?= afficher_flash() ?>
