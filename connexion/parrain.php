<?php
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/fonctions_connexion.php';

// Déjà connecté : direction la page d'accueil de son rôle
if ($u = utilisateur()) {
    rediriger(ROLES[$u['role']]['accueil']);
}

$erreur = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $erreur = traiter_connexion($pdo, 'parrain');
}

$titre_page = 'Connexion parrain, marraine, Mame';
$bandeau = ['titre' => 'Connexion parrain, marraine, Mame', 'texte' => 'Scanne le QR code d’un DUT1 et retrouve tes signatures.'];
$scripts_page = ['assets/js/connexion.js'];
require __DIR__ . '/../includes/header.php';
afficher_formulaire_connexion('parrain', $erreur);
require __DIR__ . '/../includes/footer.php';
