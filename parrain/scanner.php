<?php
/**
 * PARRAIN / MAME – Scanner le QR code d'un DUT1 avec la caméra (ou depuis une photo).
 * Le script lit le QR, en extrait « dut1 » et ouvre parrain/valider.php?dut1=…
 * La caméra exige https (ou localhost) ; sinon : appareil photo natif du téléphone ou photo du code.
 */
require_once __DIR__ . '/../includes/init.php';
$moi = exiger_role('parrain');

$titre_page = 'Scanner un QR code';
require __DIR__ . '/../includes/header.php';
?>
<section class="carte scanner-carte">
    <h1>Scanner un QR code</h1>
    <p>Placez le code du DUT1 dans le cadre pour signer.</p>

    <div id="scanner-zone" data-url-validation="<?= e(url('parrain/valider.php')) ?>">
        <div id="scanner-lecteur"></div>
        <p id="scanner-message" role="status"></p>
        <p><button type="button" class="btn" id="scanner-demarrer">Ouvrir la caméra</button></p>
        <div class="champ">
            <label for="scanner-fichier">Ou envoyez une photo du code</label>
            <input type="file" id="scanner-fichier" accept="image/*">
        </div>
    </div>
</section>

<!-- Lecture du QR : bibliothèque html5-qrcode. Script laissé ici faute de fichier JS dédié ; à déplacer dans assets/js/scanner.js si le groupe l'exige. -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.8/html5-qrcode.min.js"></script>
<script>
(function () {
    'use strict';

    var zone = document.getElementById('scanner-zone');
    if (!zone || typeof Html5Qrcode === 'undefined') { return; }

    var urlValidation = zone.dataset.urlValidation;
    var message = document.getElementById('scanner-message');
    var boutonCamera = document.getElementById('scanner-demarrer');
    var champFichier = document.getElementById('scanner-fichier');
    var lecteur = new Html5Qrcode('scanner-lecteur');
    var cameraActive = false;

    function afficherMessage(texte) { message.textContent = texte; }

    // On ne garde que le numéro du DUT1 : la page ouverte est toujours celle de ce site (pas de redirection externe).
    function extraireIdDut1(texte) {
        var correspondance = /[?&]dut1=(\d+)/.exec(texte);
        return correspondance ? correspondance[1] : null;
    }

    function ouvrirValidation(texte) {
        var id = extraireIdDut1(texte);
        if (id === null) {
            afficherMessage('Ce QR code n’est pas un code DUT1 de la plateforme.');
            return false;
        }
        window.location.href = urlValidation + '?dut1=' + id;
        return true;
    }

    function arreterCamera() {
        if (!cameraActive) { return Promise.resolve(); }
        cameraActive = false;
        return lecteur.stop().catch(function () {});
    }

    boutonCamera.addEventListener('click', function () {
        afficherMessage('');
        lecteur.start(
            { facingMode: 'environment' },
            { fps: 10, qrbox: 250 },
            function (texte) { if (ouvrirValidation(texte)) { arreterCamera(); } },
            function () {}
        ).then(function () {
            cameraActive = true;
            boutonCamera.hidden = true;
        }).catch(function () {
            afficherMessage('Impossible d’ouvrir la caméra (https ou localhost requis). '
                + 'Utilisez l’appareil photo du téléphone ou envoyez une photo du code.');
        });
    });

    champFichier.addEventListener('change', function () {
        var fichier = champFichier.files && champFichier.files[0];
        if (!fichier) { return; }
        afficherMessage('');
        arreterCamera().then(function () {
            return lecteur.scanFile(fichier, false);
        }).then(ouvrirValidation).catch(function () {
            afficherMessage('Aucun QR code lisible sur cette photo. Essayez une image plus nette.');
        });
    });
}());
</script>
<?php require __DIR__ . '/../includes/footer.php'; ?>
