<?php
require_once __DIR__ . '/../includes/init.php';

// Rôle d'origine, pour revenir sur la bonne page de connexion après la déconnexion
$role = utilisateur()['role'] ?? 'dut1';

fermer_session();
flash('info', 'Tu es déconnecté.');
rediriger(ROLES[$role]['connexion']);
