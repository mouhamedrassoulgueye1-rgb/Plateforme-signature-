<?php
/**
 * En-tête commun (charte des équipes 1-2). Dans une page :
 *   $titre_page  = 'Mes signatures';                                  // titre de l'onglet
 *   $bandeau     = ['titre' => 'Mes signatures', 'texte' => '…'];    // facultatif : grand bandeau bleu en haut
 *                  (clé facultative 'sur_titre', par défaut « SITE SIGNATURE 2027 · ESP DAKAR »)
 *   $styles_page = ['assets/css/inscription.css'];                   // facultatif : CSS propre à la page
 *   $classe_main = 'conteneur-plein';                                // facultatif : page pleine largeur
 *   require __DIR__ . '/../includes/header.php';
 */
$u = utilisateur();

/** Liens de la barre du haut selon le rôle connecté : libellé => page. */
const MENU_ENTETE = [
    'visiteur' => ['Accueil' => 'index.php', 'Espace DUT1' => 'espace/dut1.php', 'Espace parrain' => 'espace/parrain.php'],
    'dut1'     => ['Accueil' => 'dut1/accueil.php', 'Registre' => 'dut1/registre.php', 'Mes signatures' => 'dut1/signatures.php', 'Classement' => 'dut1/classement.php'],
    'parrain'  => ['Accueil' => 'parrain/accueil.php', 'Scanner' => 'parrain/scanner.php', 'Mes signatures' => 'parrain/signatures.php'],
    'admin'    => ['Tableau de bord' => 'admin/index.php'],
];
$page_actuelle = $_SERVER['SCRIPT_NAME'] ?? '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#052a63">
    <title><?= e(($titre_page ?? 'Accueil') . ' · Signature 2027') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(url('assets/css/style.css')) ?>">
    <?php foreach ($styles_page ?? [] as $feuille): ?>
        <link rel="stylesheet" href="<?= e(url($feuille)) ?>">
    <?php endforeach; ?>
</head>
<body>
<!-- Symboles réutilisables : <svg><use href="#logo"/></svg> et <svg><use href="#circuit"/></svg> -->
<svg class="symboles" aria-hidden="true">
    <symbol id="logo" viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="24" cy="24" r="21"/><path d="M14 35l3.4-10L33 9.5 38.5 15 23 30.6 14 35z"/><path d="M29.5 13l5.5 5.5M17.5 25l5.5 5.5"/>
    </symbol>
    <symbol id="circuit" viewBox="0 0 180 130" fill="none" stroke="currentColor" stroke-width="1.5">
        <path d="M0 40h50l22 -22h40M0 70h38l26 26h70M0 105h60M72 18v-18M112 18h30"/>
        <g fill="currentColor" stroke="none"><circle cx="112" cy="18" r="3.5"/><circle cx="134" cy="96" r="3.5"/><circle cx="60" cy="105" r="3.5"/><circle cx="142" cy="18" r="3.5"/></g>
    </symbol>
</svg>

<header class="entete">
    <a class="entete-logo" href="<?= e(url($u ? ROLES[$u['role']]['accueil'] : 'index.php')) ?>" aria-label="Site Signature 2027 - accueil">
        <svg aria-hidden="true"><use href="#logo"/></svg>
        <b>SITE SIGNATURE <span>2027</span></b>
    </a>
    <nav class="entete-nav" aria-label="Navigation principale">
        <?php foreach (MENU_ENTETE[$u['role'] ?? 'visiteur'] as $libelle => $page): ?>
            <a href="<?= e(url($page)) ?>" <?= $page_actuelle === url($page) ? 'aria-current="page"' : '' ?>><?= e($libelle) ?></a>
        <?php endforeach; ?>
        <?php if ($u): ?>
            <span class="entete-utilisateur">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>
                <?= e($u['role'] === 'admin' ? 'Admin' : $u['prenom']) ?>
            </span>
            <a href="<?= e(url('connexion/deconnexion.php')) ?>">Déconnexion</a>
        <?php endif; ?>
    </nav>
</header>

<?php if (!empty($bandeau)): ?>
    <section class="bandeau">
        <svg class="bandeau-circuit" aria-hidden="true"><use href="#circuit"/></svg>
        <div class="bandeau-halo" aria-hidden="true"></div>
        <div class="bandeau-contenu">
            <p class="bandeau-sur-titre"><?= e($bandeau['sur_titre'] ?? 'SITE SIGNATURE 2027 · ESP DAKAR') ?></p>
            <h1><?= e($bandeau['titre']) ?></h1>
            <?php if (!empty($bandeau['texte'])): ?>
                <p class="bandeau-texte"><?= e($bandeau['texte']) ?></p>
            <?php endif; ?>
        </div>
    </section>
<?php endif; ?>

<main class="<?= e($classe_main ?? ('conteneur' . (!empty($bandeau) ? ' conteneur-sous-bandeau' : ''))) ?>">
<?= afficher_flash() ?>
