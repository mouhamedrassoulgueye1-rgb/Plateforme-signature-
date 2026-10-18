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
    <a class="entete-logo" href="<?= e(url($u ? ROLES[$u['role']]['accueil'] : 'index.php')) ?>">Signature 2027</a>
    <?php if ($u): ?>
        <nav class="entete-nav">
            <span class="entete-utilisateur"><?= e($u['prenom']) ?></span>
            <a href="<?= e(url('connexion/deconnexion.php')) ?>">Déconnexion</a>
        </nav>
    <?php endif; ?>
</header>
<main class="conteneur">
<?= afficher_flash() ?>
