<?php
require_once __DIR__ . '/includes/init.php';

// Déjà connecté : direction la page d'accueil de son rôle
if ($u = utilisateur()) {
    rediriger(ROLES[$u['role']]['accueil']);
}

$titre_page = 'Bienvenue';
require __DIR__ . '/includes/header.php';
?>
<section class="carte centre">
    <h1>Signature 2027</h1>
    <p>Un lien aujourd’hui, une force demain !</p>
    <div class="groupe-boutons">
        <a class="btn" href="<?= e(url('connexion/dut1.php')) ?>">Je suis DUT1</a>
        <a class="btn btn-secondaire" href="<?= e(url('connexion/parrain.php')) ?>">Je suis parrain, marraine ou Mame</a>
    </div>
    <p class="petit">
        Pas encore de compte ?
        <a href="<?= e(url('inscription/dut1.php')) ?>">Inscription DUT1</a> ·
        <a href="<?= e(url('inscription/parrain.php')) ?>">Inscription parrain</a>
    </p>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
