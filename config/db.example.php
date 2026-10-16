<?php
// Modèle de configuration.
// 1. Copiez ce fichier en config/db.php
// 2. Adaptez les valeurs à votre machine.
// config/db.php n'est jamais envoyé sur GitHub (voir .gitignore) : chacun garde le sien.

define('DB_HOTE', 'localhost');
define('DB_NOM', 'signature2027');
define('DB_UTILISATEUR', 'root');
define('DB_MOT_DE_PASSE', '');

// Adresse du site, sans « / » final. En local avec XAMPP : '/signature2027'. En production : ''.
define('BASE_URL', '/signature2027');
