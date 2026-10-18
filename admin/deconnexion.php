<?php
require_once __DIR__ . '/../includes/init.php';
fermer_session();
flash('info', 'Tu es déconnecté.');
rediriger('admin/connexion.php');
