<?php
/**
 * DUT1 – Téléchargement de la liste des personnes qui ont signé pour moi (CSV).
 * Séparateur « ; » et BOM UTF-8 : s'ouvre correctement dans Excel en français.
 */
require __DIR__ . '/commun.php';
require __DIR__ . '/chambres.php';

$dut1 = exiger_filleul($pdo);
$idFilleul = (int) $dut1['id_filleul'];

$st = $pdo->prepare('SELECT nom_utilisateur FROM filleul WHERE id_filleul = ?');
$st->execute([$idFilleul]);
$login = (string) $st->fetchColumn();

$st = $pdo->prepare(
    'SELECT p.prenom, p.nom, p.type_parrain, p.chambre, p.telephone, s.date_signature
     FROM signature s
     JOIN parrain p ON p.id_parrain = s.id_parrain
     WHERE s.id_filleul = ?
     ORDER BY s.date_signature'
);
$st->execute([$idFilleul]);

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="signatures_' . preg_replace('/[^A-Za-z0-9_-]/', '', $login) . '.csv"');

$out = fopen('php://output', 'w');
fwrite($out, "\xEF\xBB\xBF");
fputcsv($out, ['Prénom', 'Nom', 'Type', 'Chambre', 'Téléphone', 'Date de signature'], ';', '"', '');
while ($l = $st->fetch()) {
    fputcsv($out, [
        cellule_sure($l['prenom']), cellule_sure($l['nom']), $l['type_parrain'],
        analyser_chambre($l['chambre'])['affiche'], $l['telephone'], $l['date_signature'],
    ], ';', '"', '');
}
fclose($out);
