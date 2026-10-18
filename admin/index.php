<?php
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/fonctions_admin.php';
$moi = exiger_role('admin');

// Chiffres clés
$nb_dut1 = (int) $pdo->query('SELECT COUNT(*) FROM filleul')->fetchColumn();
$nb_parrains = (int) $pdo->query('SELECT COUNT(*) FROM parrain')->fetchColumn();
$nb_signatures = (int) $pdo->query('SELECT COUNT(*) FROM signature')->fetchColumn();
$nb_actifs = (int) $pdo->query(
    "SELECT (SELECT COUNT(*) FROM filleul WHERE statut_compte = 'actif')
          + (SELECT COUNT(*) FROM parrain WHERE statut_compte = 'actif')"
)->fetchColumn();
$nb_aujourdhui = (int) $pdo->query('SELECT COUNT(*) FROM signature WHERE DATE(date_signature) = CURDATE()')->fetchColumn();
$nb_suspendus = $nb_dut1 + $nb_parrains - $nb_actifs;
$moyenne = $nb_dut1 > 0 ? number_format($nb_signatures / $nb_dut1, 1, ',', ' ') : '0';

// Dernières inscriptions (DUT1 et signataires mélangés)
$inscriptions = $pdo->query(
    "SELECT * FROM (
        SELECT 'dut1' AS role, id_filleul AS id, nom, prenom, 'DUT1' AS libelle, date_inscription FROM filleul
        UNION ALL
        SELECT 'parrain', id_parrain, nom, prenom, type_parrain, date_inscription FROM parrain
     ) AS t ORDER BY date_inscription DESC LIMIT 8"
)->fetchAll();

// Dernières signatures
$dernieres = $pdo->query(
    'SELECT s.date_signature, p.id_parrain, p.prenom AS p_prenom, p.nom AS p_nom, p.type_parrain,
            f.id_filleul, f.prenom AS f_prenom, f.nom AS f_nom
     FROM signature s
     JOIN parrain p ON p.id_parrain = s.id_parrain
     JOIN filleul f ON f.id_filleul = s.id_filleul
     ORDER BY s.date_signature DESC
     LIMIT 8'
)->fetchAll();

$cartes = [
    ['DUT1 inscrits', $nb_dut1, 'personne', 'bleu'],
    ['Parrains, Marraines, Mames', $nb_parrains, 'groupe', 'orange'],
    ['Signatures totales', $nb_signatures, 'valide', 'vert'],
    ['Utilisateurs actifs', $nb_actifs, 'actifs', 'violet'],
];

admin_debut('Tableau de bord', 'tableau');
?>
<div class="admin-titre">
    <h1>Tableau de bord</h1>
    <a class="btn" href="<?= e(url('admin/utilisateur_creer.php')) ?>"><?= admin_icone('ajouter', 18) ?> Créer un utilisateur</a>
</div>

<div class="admin-stats">
    <?php foreach ($cartes as [$libelle, $valeur, $icone, $teinte]): ?>
        <div class="admin-stat">
            <span class="admin-stat-icone admin-teinte-<?= e($teinte) ?>"><?= admin_icone($icone, 22) ?></span>
            <span class="admin-stat-libelle"><?= e($libelle) ?></span>
            <span class="admin-stat-valeur"><?= e(number_format($valeur, 0, ',', ' ')) ?></span>
        </div>
    <?php endforeach; ?>
</div>

<p class="admin-resume">
    <span><strong><?= e($nb_aujourdhui) ?></strong> signature<?= $nb_aujourdhui > 1 ? 's' : '' ?> aujourd’hui</span>
    <span><strong><?= e($moyenne) ?></strong> signatures par DUT1 en moyenne</span>
    <a class="<?= $nb_suspendus > 0 ? 'admin-resume-alerte' : '' ?>" href="<?= e(url('admin/utilisateurs.php?statut=suspendu')) ?>"><strong><?= e($nb_suspendus) ?></strong> compte<?= $nb_suspendus > 1 ? 's' : '' ?> suspendu<?= $nb_suspendus > 1 ? 's' : '' ?></a>
</p>

<div class="admin-colonnes">
    <section class="carte">
        <div class="admin-titre-section">
            <h2>Dernières inscriptions</h2>
            <a href="<?= e(url('admin/utilisateurs.php')) ?>">Tout voir</a>
        </div>
        <?php if (!$inscriptions): ?>
            <p class="petit">Aucune inscription pour l’instant.</p>
        <?php else: ?>
            <table class="tableau">
                <thead><tr><th>Nom</th><th>Rôle</th><th>Date</th></tr></thead>
                <tbody>
                <?php foreach ($inscriptions as $i): ?>
                    <tr>
                        <td><a href="<?= e(admin_url('utilisateur_voir.php', $i['role'], (int) $i['id'])) ?>"><?= e($i['nom'] . ' ' . $i['prenom']) ?></a></td>
                        <td><span class="admin-role admin-role-<?= e($i['role']) ?>"><?= e($i['libelle']) ?></span></td>
                        <td><?= e(date('d/m/Y', strtotime($i['date_inscription']))) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>

    <section class="carte">
        <div class="admin-titre-section">
            <h2>Dernières signatures</h2>
            <a href="<?= e(url('admin/signatures.php')) ?>">Gérer</a>
        </div>
        <?php if (!$dernieres): ?>
            <p class="petit">Aucune signature pour l’instant.</p>
        <?php else: ?>
            <table class="tableau">
                <thead><tr><th>Signataire</th><th>Pour</th><th>Quand</th></tr></thead>
                <tbody>
                <?php foreach ($dernieres as $s): ?>
                    <tr>
                        <td><?= e($s['p_nom'] . ' ' . $s['p_prenom']) ?> <span class="petit"><?= e($s['type_parrain']) ?></span></td>
                        <td><a href="<?= e(url('admin/signatures.php?dut1=' . $s['id_filleul'])) ?>"><?= e($s['f_nom'] . ' ' . $s['f_prenom']) ?></a></td>
                        <td><?= e(date('d/m H:i', strtotime($s['date_signature']))) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>
</div>
<?php admin_fin(); ?>
