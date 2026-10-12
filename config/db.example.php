<?php
// Copiez ce fichier en db.php et adaptez les identifiants
$pdo = new PDO('mysql:host=localhost;dbname=signature2027;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);
