<?php
/**
 * PARRAIN / MAME – Scanner le QR code d'un DUT1 avec la caméra (ou depuis une photo).
 * Le JavaScript (parrain.js) lit le QR, en extrait id_filleul et envoie vers valider.php.
 * La caméra exige https (ou localhost) ; sinon : appareil photo natif du téléphone.
 */
require __DIR__ . '/commun.php';

exiger_parrain($pdo);

$contenu = vue('scanner', [
    'html_entete' => vue('entete', ['titre' => 'Scanner un QR code', 'sous_titre' => 'Place le code du DUT1 dans le cadre pour signer.']),
    'html_pied'   => vue('pied'),
]);

echo page('Scanner', $contenu, [
    'https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.8/html5-qrcode.min.js',
    'parrain.js',
], 'scanner');
