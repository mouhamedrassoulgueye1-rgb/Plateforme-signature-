<?php
/**
 * Exports CSV (ouverts directement par Excel) : ?fichier=dut1 | signataires | signatures | classement
 * Séparateur « ; » et encodage UTF-8 avec BOM, comme l'attend Excel en français.
 */
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/fonctions_admin.php';
$moi = exiger_role('admin');

$exports = [
    'dut1' => [
        'DUT1 inscrits',
        'Nom, prénom, identifiant, téléphone, statut, date d’inscription et nombre de signatures reçues.',
        ['Nom', 'Prénom', 'Identifiant', 'Téléphone', 'Statut', 'Inscription', 'Signatures reçues'],
        'SELECT f.nom, f.prenom, f.nom_utilisateur, f.telephone, f.statut_compte, f.date_inscription, COUNT(s.id_parrain)
         FROM filleul f LEFT JOIN signature s ON s.id_filleul = f.id_filleul
         GROUP BY f.id_filleul, f.nom, f.prenom, f.nom_utilisateur, f.telephone, f.statut_compte, f.date_inscription
         ORDER BY f.nom, f.prenom',
    ],
    'signataires' => [
        'Parrains, marraines et Mames',
        'Nom, prénom, type, chambre, téléphone, statut et nombre de signatures données.',
        ['Nom', 'Prénom', 'Identifiant', 'Type', 'Chambre', 'Téléphone', 'Statut', 'Inscription', 'Signatures données'],
        'SELECT p.nom, p.prenom, p.nom_utilisateur, p.type_parrain, p.chambre, p.telephone, p.statut_compte, p.date_inscription, COUNT(s.id_filleul)
         FROM parrain p LEFT JOIN signature s ON s.id_parrain = p.id_parrain
         GROUP BY p.id_parrain, p.nom, p.prenom, p.nom_utilisateur, p.type_parrain, p.chambre, p.telephone, p.statut_compte, p.date_inscription
         ORDER BY p.nom, p.prenom',
    ],
    'signatures' => [
        'Toutes les signatures',
        'Qui a signé pour qui, et quand.',
        ['Date', 'Signataire', 'Type', 'Chambre', 'DUT1', 'Téléphone DUT1'],
        "SELECT s.date_signature, CONCAT(p.nom, ' ', p.prenom), p.type_parrain, p.chambre, CONCAT(f.nom, ' ', f.prenom), f.telephone
         FROM signature s JOIN parrain p ON p.id_parrain = s.id_parrain JOIN filleul f ON f.id_filleul = s.id_filleul
         ORDER BY s.date_signature",
    ],
    'classement' => [
        'Classement des DUT1',
        'Rang, nom, prénom, téléphone et nombre de signatures.',
        ['Rang', 'Nom', 'Prénom', 'Téléphone', 'Signatures'],
        null, // calculé plus bas
    ],
];

$fichier = (string) ($_GET['fichier'] ?? '');
if (isset($exports[$fichier])) {
    [$titre, , $entetes, $sql] = $exports[$fichier];
    if ($fichier === 'classement') {
        $lignes = array_map(fn($l) => [$l['rang'] ?? 'suspendu', $l['nom'], $l['prenom'], $l['telephone'], $l['nb_signatures']], classement_filleuls($pdo, true));
    } else {
        $lignes = $pdo->query($sql)->fetchAll(PDO::FETCH_NUM);
        // Colonne « Type » : libellé réglé dans admin/libelles.php
        $col_type = ['signataires' => 3, 'signatures' => 2][$fichier] ?? null;
        if ($col_type !== null) {
            foreach ($lignes as &$l) {
                $l[$col_type] = libelle_type($l[$col_type]);
            }
            unset($l);
        }
    }
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="signature2027_' . $fichier . '_' . date('Y-m-d') . '.csv"');
    $sortie = fopen('php://output', 'w');
    fwrite($sortie, "\xEF\xBB\xBF");
    fputcsv($sortie, $entetes, ';', '"', '');
    foreach ($lignes as $l) {
        // Empêche Excel d'interpréter une cellule comme une formule (=, +, -, @)
        $l = array_map(fn($v) => is_string($v) && preg_match('/^[=+\-@]/', $v) ? "'" . $v : $v, $l);
        fputcsv($sortie, $l, ';', '"', '');
    }
    fclose($sortie);
    exit;
}

admin_debut('Téléchargements', 'telechargements');
?>
<h1>Téléchargements</h1>
<p class="petit">Fichiers CSV, à ouvrir avec Excel, LibreOffice ou Google Sheets.</p>
<div class="admin-exports">
    <?php foreach ($exports as $cle => [$titre, $description]): ?>
        <a class="carte admin-export" href="<?= e(url('admin/telechargements.php?fichier=' . $cle)) ?>">
            <span class="admin-stat-icone admin-teinte-bleu"><?= admin_icone('telechargements', 22) ?></span>
            <span><strong><?= e($titre) ?></strong><span class="petit"><?= e($description) ?></span></span>
        </a>
    <?php endforeach; ?>
</div>
<?php admin_fin(); ?>
