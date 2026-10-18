<?php
require_once __DIR__ . '/../includes/init.php';
$moi = exiger_role('admin');

// Chiffres clés
$nb_dut1 = (int) $pdo->query('SELECT COUNT(*) FROM filleul')->fetchColumn();
$nb_parrains = (int) $pdo->query('SELECT COUNT(*) FROM parrain')->fetchColumn();
$nb_signatures = (int) $pdo->query('SELECT COUNT(*) FROM signature')->fetchColumn();
$nb_aujourdhui = (int) $pdo->query('SELECT COUNT(*) FROM signature WHERE DATE(date_signature) = CURDATE()')->fetchColumn();
$nb_suspendus = (int) $pdo->query(
    "SELECT (SELECT COUNT(*) FROM filleul WHERE statut_compte = 'suspendu')
          + (SELECT COUNT(*) FROM parrain WHERE statut_compte = 'suspendu')"
)->fetchColumn();
$moyenne = $nb_dut1 > 0 ? round($nb_signatures / $nb_dut1, 1) : 0;

// Les 10 DUT1 qui ont le plus de signatures
$classement = $pdo->query(
    'SELECT f.id_filleul, f.prenom, f.nom, f.nom_utilisateur, COUNT(s.id_parrain) AS nb
     FROM filleul f LEFT JOIN signature s ON s.id_filleul = f.id_filleul
     GROUP BY f.id_filleul, f.prenom, f.nom, f.nom_utilisateur
     ORDER BY nb DESC, f.nom, f.prenom
     LIMIT 10'
)->fetchAll();

// Les 10 dernières signatures
$dernieres = $pdo->query(
    'SELECT s.date_signature, p.prenom AS p_prenom, p.nom AS p_nom, p.type_parrain,
            f.prenom AS f_prenom, f.nom AS f_nom
     FROM signature s
     JOIN parrain p ON p.id_parrain = s.id_parrain
     JOIN filleul f ON f.id_filleul = s.id_filleul
     ORDER BY s.date_signature DESC
     LIMIT 10'
)->fetchAll();

$titre_page = 'Tableau de bord';
require __DIR__ . '/../includes/header.php';
?>
<h1><?= e($titre_page) ?></h1>

<nav class="admin-menu">
    <a class="btn" href="<?= e(url('admin/utilisateurs.php')) ?>">Utilisateurs</a>
    <a class="btn btn-secondaire" href="<?= e(url('admin/utilisateur_creer.php')) ?>">Créer un utilisateur</a>
    <a class="btn btn-secondaire" href="<?= e(url('admin/signatures.php')) ?>">Gérer les signatures</a>
    <a class="btn btn-secondaire" href="<?= e(url('admin/mot_de_passe.php')) ?>">Mon mot de passe</a>
</nav>

<div class="admin-stats">
    <div class="carte admin-stat"><span class="admin-stat-valeur"><?= e($nb_dut1) ?></span>DUT1 inscrits</div>
    <div class="carte admin-stat"><span class="admin-stat-valeur"><?= e($nb_parrains) ?></span>Parrains, marraines et Mames</div>
    <div class="carte admin-stat"><span class="admin-stat-valeur"><?= e($nb_signatures) ?></span>Signatures au total</div>
    <div class="carte admin-stat"><span class="admin-stat-valeur"><?= e($nb_aujourdhui) ?></span>Signatures aujourd’hui</div>
    <div class="carte admin-stat"><span class="admin-stat-valeur"><?= e($moyenne) ?></span>Signatures par DUT1 en moyenne</div>
    <div class="carte admin-stat <?= $nb_suspendus > 0 ? 'admin-stat-alerte' : '' ?>">
        <span class="admin-stat-valeur"><?= e($nb_suspendus) ?></span>Comptes suspendus
    </div>
</div>

<div class="admin-colonnes">
    <section class="carte">
        <h2>Classement des DUT1</h2>
        <?php if (!$classement): ?>
            <p class="petit">Aucun DUT1 inscrit pour l’instant.</p>
        <?php else: ?>
            <table class="tableau">
                <thead><tr><th>Rang</th><th>DUT1</th><th>Signatures</th></tr></thead>
                <tbody>
                <?php $rang = 0; $precedent = null; foreach ($classement as $i => $l):
                    if ($l['nb'] !== $precedent) { $rang = $i + 1; $precedent = $l['nb']; } ?>
                    <tr>
                        <td><?= e($rang) ?></td>
                        <td><a href="<?= e(url('admin/utilisateur_voir.php?role=dut1&id=' . $l['id_filleul'])) ?>"><?= e($l['prenom'] . ' ' . $l['nom']) ?></a></td>
                        <td><?= e($l['nb']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>

    <section class="carte">
        <h2>Dernières signatures</h2>
        <?php if (!$dernieres): ?>
            <p class="petit">Aucune signature pour l’instant.</p>
        <?php else: ?>
            <table class="tableau">
                <thead><tr><th>Quand</th><th>Signataire</th><th>Pour</th></tr></thead>
                <tbody>
                <?php foreach ($dernieres as $s): ?>
                    <tr>
                        <td><?= e(date('d/m H:i', strtotime($s['date_signature']))) ?></td>
                        <td><?= e($s['p_prenom'] . ' ' . $s['p_nom']) ?> <span class="petit">(<?= e($s['type_parrain']) ?>)</span></td>
                        <td><?= e($s['f_prenom'] . ' ' . $s['f_nom']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
