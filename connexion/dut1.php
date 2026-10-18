<?php
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/fonctions_connexion.php';

// Déjà connecté : direction la page d'accueil de son rôle
if ($u = utilisateur()) {
    rediriger(ROLES[$u['role']]['accueil']);
}

$erreur = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $erreur = traiter_connexion($pdo, 'dut1');
}

$titre_page = 'Connexion DUT1';
$bandeau = ['titre' => 'Connexion DUT1', 'texte' => 'Retrouve ton compteur, ton code QR et ta liste de signatures.'];
$scripts_page = ['assets/js/connexion.js'];
require __DIR__ . '/../includes/header.php';
afficher_formulaire_connexion('dut1', $erreur);
require __DIR__ . '/../includes/footer.php';
