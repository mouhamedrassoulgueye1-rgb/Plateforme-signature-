<?php
/**
 * Équipe 5 : API JSON de la page « Mes signatures ».
 *   GET dut1/signatures_api.php  →  nombre, rang, total de DUT1, objectif et signataires du DUT1 connecté.
 * assets/js/signatures_dut1.js l'appelle toutes les 30 secondes. Documentation : dut1/signatures_api.md.
 * Codes : 200, 401 (pas connecté en DUT1 ou compte suspendu), 405 (autre méthode que GET), 500 (erreur serveur).
 * L'id du DUT1 vient de la session, jamais de l'URL.
 */
require_once __DIR__ . '/signatures_fonctions.php';
exiger_get();

try {
    // init.php est dans le try : si la base est injoignable, on renvoie un 500 propre
    // au lieu d'une erreur PHP qui afficherait des chemins et des détails techniques.
    require_once __DIR__ . '/../includes/init.php';
    $moi = exiger_dut1_api($pdo);
    repondre_json(200, lire_donnees_signatures($pdo, $moi['id']));
} catch (Throwable $ex) {
    repondre_erreur_serveur($ex);
}
