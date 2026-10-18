<?php
require_once __DIR__ . '/../includes/init.php';
fermer_session();
flash('info', 'Vous êtes déconnecté.');
rediriger('admin/connexion.php');
