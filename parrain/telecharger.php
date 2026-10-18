<?php
/**
 * PARRAIN / MAME – Téléchargement (CSV) de la liste des DUT1 pour lesquels j'ai signé.
 * Séparateur « ; » et BOM UTF-8 : s'ouvre correctement dans Excel en français.
 */
require_once __DIR__ . '/../includes/init.php';
$moi = exiger_role('parrain');

// Neutralise les cellules qui commencent par = + - @ (injection de formule Excel)
$securiser = fn(string $valeur): string => preg_match('/^[=+\-@\t\r]/', $valeur) ? "'" . $valeur : $valeur;
// Téléphone (chiffres seuls en base) écrit pour qu'Excel l'affiche en entier
$telephone = function (?string $valeur): string {
    $chiffres = preg_replace('/\D+/', '', (string) $valeur);
    return $chiffres === '' ? '' : '="' . $chiffres . '"';
};

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="mes_signatures_' . preg_replace('/[^A-Za-z0-9_.-]/', '', $moi['nom_utilisateur']) . '.csv"');

$sortie = fopen('php://output', 'w');
fwrite($sortie, "\xEF\xBB\xBF");
fputcsv($sortie, ['Prénom', 'Nom', 'Téléphone', 'Date de signature'], ';', '"', '');
foreach (filleuls_signes_par($pdo, $moi['id']) as $f) {
    fputcsv($sortie, [
        $securiser($f['prenom']),
        $securiser($f['nom']),
        $telephone($f['telephone']),
        (new DateTime($f['date_signature']))->format('d/m/Y H:i'),
    ], ';', '"', '');
}
fclose($sortie);
