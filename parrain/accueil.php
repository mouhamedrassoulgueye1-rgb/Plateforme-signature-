<?php
/** PARRAIN / MAME – Accueil : nombre de signatures données, bouton « Scanner un QR code », dernières signatures, mode d'emploi. */
require_once __DIR__ . '/../includes/init.php';
$moi = exiger_role('parrain');

/** Photo de la personne (colonne « photo ») ou, à défaut, ses initiales. */
if (!function_exists('html_avatar')) {
    function html_avatar(array $personne, int $taille = 44): string
    {
        if (!empty($personne['photo'])) {
            return '<img class="avatar-signataire" width="' . $taille . '" height="' . $taille . '" src="' . e(url($personne['photo'])) . '" alt="">';
        }
        $initiales = mb_strtoupper(mb_substr((string) $personne['prenom'], 0, 1) . mb_substr((string) $personne['nom'], 0, 1));
        return '<strong class="avatar-signataire avatar-signataire-initiales" aria-hidden="true">' . e($initiales) . '</strong>';
    }
}

$nb         = nb_signatures_parrain($pdo, $moi['id']);   // calculé à chaque affichage, jamais stocké
$total_dut1 = nb_filleuls_actifs($pdo);

$req = $pdo->prepare(
    'SELECT f.id_filleul, f.prenom, f.nom, f.photo, s.date_signature
     FROM signature s JOIN filleul f ON f.id_filleul = s.id_filleul
     WHERE s.id_parrain = ? ORDER BY s.date_signature DESC LIMIT 5'
);
$req->execute([$moi['id']]);
$dernieres = $req->fetchAll();

$titre_page = 'Accueil';
require __DIR__ . '/../includes/header.php';
?>
<section class="carte accueil-parrain-bienvenue">
    <h1>Bienvenue <?= e($moi['prenom']) ?> !</h1>
    <p>Scannez le QR code d’un DUT1 pour signer.</p>
    <p class="accueil-parrain-nombre"><strong><?= e($nb) ?></strong> sur <?= e($total_dut1) ?> DUT1</p>
    <p><?= $nb === 0 ? 'Vous n’avez pas encore signé pour un DUT1.' : 'Merci pour votre engagement !' ?></p>
    <p><a class="btn" href="<?= e(url('parrain/scanner.php')) ?>">Scanner un QR code</a></p>
</section>

<section class="carte accueil-parrain-dernieres">
    <h2>Dernières signatures</h2>
    <?php if ($dernieres): ?>
        <ul class="signataire-liste">
            <?php foreach ($dernieres as $f): ?>
                <li class="signataire-ligne">
                    <?= html_avatar($f) ?>
                    <a href="<?= e(url('parrain/signatures.php?dut1=' . (int) $f['id_filleul'])) ?>"><strong><?= e($f['prenom'] . ' ' . $f['nom']) ?></strong></a>
                    <small>Signé le <?= e((new DateTime($f['date_signature']))->format('d/m/Y H:i')) ?></small>
                </li>
            <?php endforeach; ?>
        </ul>
        <p><a href="<?= e(url('parrain/signatures.php')) ?>">Voir toutes mes signatures</a> ·
           <a href="<?= e(url('parrain/telecharger.php')) ?>">Télécharger (CSV)</a></p>
    <?php else: ?>
        <p>Vous n’avez encore signé pour aucun DUT1.</p>
    <?php endif; ?>
</section>

<section class="carte accueil-parrain-mode-emploi">
    <h2>Comment signer ?</h2>
    <ol>
        <li>Demandez au DUT1 d’afficher son code QR (page « Accueil » de son espace).</li>
        <li>Scannez-le avec « Scanner un QR code » ou avec l’appareil photo de votre téléphone.</li>
        <li>Vérifiez la personne puis confirmez votre signature.</li>
    </ol>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
