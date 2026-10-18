<?php
/** Connexion des DUT1 (table filleul). Appelé par connexion-dut1.js. */
declare(strict_types=1);

require __DIR__ . '/../config/db.php';                // doit définir $pdo (connexion PDO)
require __DIR__ . '/../includes/connexion_commune.php';

connecter($pdo, [
    'role'     => 'dut1',
    'table'    => 'filleul',
    'id'       => 'id_filleul',
    'statut'   => true,
    'redirect' => 'dut1/accueil.php',
]);