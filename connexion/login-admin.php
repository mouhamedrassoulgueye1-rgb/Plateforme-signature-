<?php
/** Connexion de l'administrateur (table administrateur, sans statut_compte). Appelé par connexion-admin.js. */
declare(strict_types=1);

require __DIR__ . '/../config/db.php';                // doit définir $pdo (connexion PDO)
require __DIR__ . '/../includes/connexion_commune.php';

connecter($pdo, [
    'role'     => 'admin',
    'table'    => 'administrateur',
    'id'       => 'id_admin',
    'statut'   => false,
    'redirect' => 'admin/index.php',
]);