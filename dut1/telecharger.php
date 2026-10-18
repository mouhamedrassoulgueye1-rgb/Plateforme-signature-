<?php
/**
 * DUT1 – Téléchargement (CSV) de la liste des personnes qui ont signé pour moi.
 * Séparateur « ; » et BOM UTF-8 : s'ouvre correctement dans Excel en français.
 */
require_once __DIR__ . '/../includes/init.php';
$moi = exiger_role('dut1');
require_once __DIR__ . '/chambres.php';

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="signatures_' . preg_replace('/[^A-Za-z0-9_.-]/', '', $moi['nom_utilisateur']) . '.csv"');

$sortie = fopen('php://output', 'w');
fwrite($sortie, "\xEF\xBB\xBF");
fputcsv($sortie, ['Prénom', 'Nom', 'Type', 'Chambre', 'Téléphone', 'Date de signature'], ';', '"', '');
foreach (signataires_du_filleul($pdo, $moi['id']) as $s) {
    fputcsv($sortie, [
        securiser_cellule_csv($s['prenom']),
        securiser_cellule_csv($s['nom']),
        libelle_type_parrain($s['type_parrain']),
        analyser_chambre($s['chambre'])['affiche'],
        formater_telephone_csv($s['telephone']),
        formater_date($s['date_signature']),
    ], ';', '"', '');
}
fclose($sortie);
