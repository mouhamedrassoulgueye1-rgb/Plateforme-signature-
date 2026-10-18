<?php
/** Connexion des parrains, marraines et Mames (table parrain). Appelé par connexion-parrain.js. */
declare(strict_types=1);

require __DIR__ . '/../config/db.php';                // doit définir $pdo (connexion PDO)
require __DIR__ . '/../includes/connexion_commune.php';

connecter($pdo, [
    'role'     => 'parrain',
    'table'    => 'parrain',
    'id'       => 'id_parrain',
    'statut'   => true,
    'redirect' => 'parrain/accueil.php',
]);