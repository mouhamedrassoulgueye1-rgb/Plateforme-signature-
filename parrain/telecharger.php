<?php
/**
 * PARRAIN / MAME – Téléchargement de la liste des DUT1 pour lesquels j'ai signé (CSV).
 * Séparateur « ; » et BOM UTF-8 : s'ouvre correctement dans Excel en français.
 */
require __DIR__ . '/commun.php';

$p = exiger_parrain($pdo);

$st = $pdo->prepare(
    'SELECT f.prenom, f.nom, f.telephone, s.date_signature
     FROM signature s JOIN filleul f ON f.id_filleul = s.id_filleul
     WHERE s.id_parrain = ? ORDER BY s.date_signature'
);
$st->execute([(int) $p['id_parrain']]);

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="mes_signatures_' . preg_replace('/[^A-Za-z0-9_-]/', '', $p['nom_utilisateur']) . '.csv"');

$out = fopen('php://output', 'w');
fwrite($out, "\xEF\xBB\xBF");
fputcsv($out, ['Prénom', 'Nom', 'Téléphone', 'Date de signature'], ';', '"', '');
while ($l = $st->fetch()) {
    fputcsv($out, [cellule_sure($l['prenom']), cellule_sure($l['nom']), $l['telephone'], $l['date_signature']], ';', '"', '');
}
fclose($out);
