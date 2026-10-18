<?php
require_once __DIR__ . '/includes/init.php';

// Déjà connecté : direction la page d'accueil de son rôle
if ($u = utilisateur()) {
    rediriger(ROLES[$u['role']]['accueil']);
}

$titre_page = 'Bienvenue';
$bandeau = ['titre' => 'SITE SIGNATURE 2027', 'texte' => 'Un lien aujourd’hui, une force demain !', 'sur_titre' => 'ESP Dakar · Département Informatique'];
require __DIR__ . '/includes/header.php';
?>
<section class="carte carte-etroite centre">
    <h2>Tu es…</h2>
    <div class="groupe-boutons">
        <a class="btn btn-large" href="<?= e(url('espace/dut1.php')) ?>">DUT1</a>
        <a class="btn btn-large btn-secondaire" href="<?= e(url('espace/parrain.php')) ?>">Parrain, marraine ou Mame</a>
    </div>
    <p class="petit">
        Pas encore de compte ?
        <a href="<?= e(url('inscription/dut1.php')) ?>">Inscription DUT1</a> ·
        <a href="<?= e(url('inscription/parrain.php')) ?>">Inscription parrain</a>
    </p>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
