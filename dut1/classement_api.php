<?php
/**
 * Équipe 5 : API JSON de la page « Classement ».
 *   GET dut1/classement_api.php  →  tous les DUT1 actifs classés, statistiques globales et objectif.
 * assets/js/classement.js l'appelle toutes les 30 secondes. Documentation : dut1/signatures_api.md.
 * Codes : 200, 401 (pas connecté en DUT1 ou compte suspendu), 405 (autre méthode que GET), 500 (erreur serveur).
 */
require_once __DIR__ . '/signatures_fonctions.php';
exiger_get();

try {
    // init.php dans le try : base injoignable → 500 propre, sans détail technique
    require_once __DIR__ . '/../includes/init.php';
    $moi = exiger_dut1_api($pdo);
    repondre_json(200, lire_classement($pdo, $moi['id']));
} catch (Throwable $ex) {
    repondre_erreur_serveur($ex);
}
