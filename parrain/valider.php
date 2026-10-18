<?php
/**
 * PARRAIN / MAME – CIBLE DU QR CODE d'un DUT1 (adresse encodée par dut1/accueil.php) :
 *   valider.php?dut1=12
 * GET  → carte du DUT1 + bouton « Confirmer ma signature » (évite une signature par simple lien)
 * POST → enregistrer_signature() ; une 2e tentative renvoie 'deja_signe' (RG6)
 */
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

$id_dut1 = (int) ($_GET['dut1'] ?? 0);
$req = $pdo->prepare('SELECT id_filleul, prenom, nom, photo FROM filleul WHERE id_filleul = ? AND statut_compte = \'actif\'');
$req->execute([$id_dut1]);
$dut1 = $req->fetch() ?: null;

if ($dut1 !== null && $_SERVER['REQUEST_METHOD'] === 'POST') {
    verifier_csrf();
    $resultat = enregistrer_signature($pdo, $moi['id'], $id_dut1);
    flash($resultat === 'ok' ? 'succes' : 'erreur', MESSAGES_SIGNATURE[$resultat]);
    rediriger('parrain/accueil.php');
}

$deja_signe = false;
if ($dut1 !== null) {
    $req = $pdo->prepare('SELECT COUNT(*) FROM signature WHERE id_parrain = ? AND id_filleul = ?');
    $req->execute([$moi['id'], $id_dut1]);
    $deja_signe = (int) $req->fetchColumn() > 0;
} else {
    http_response_code(404);
}

$titre_page = $dut1 === null ? 'QR code invalide' : 'Confirmer la signature';
require __DIR__ . '/../includes/header.php';
?>
<section class="carte valider-carte">
<?php if ($dut1 === null): ?>
    <h1>QR code invalide</h1>
    <div class="alerte alerte-erreur" role="alert">Ce code ne correspond à aucun DUT1 actif.</div>
    <p><a class="btn" href="<?= e(url('parrain/scanner.php')) ?>">Scanner un autre code</a></p>
<?php else: ?>
    <h1>Confirmer la signature</h1>
    <p>Vérifiez la personne avant de confirmer.</p>
    <p><?= html_avatar($dut1, 96) ?></p>
    <p class="valider-nom"><strong><?= e($dut1['prenom'] . ' ' . $dut1['nom']) ?></strong></p>
    <?php if ($deja_signe): ?>
        <div class="alerte alerte-info" role="status">Vous avez déjà signé pour cette personne.</div>
        <p><a class="btn" href="<?= e(url('parrain/accueil.php')) ?>">Retour à l’accueil</a></p>
    <?php else: ?>
        <form class="formulaire" method="post" action="<?= e(url('parrain/valider.php?dut1=' . $id_dut1)) ?>">
            <?= champ_csrf() ?>
            <button class="btn">Confirmer ma signature</button>
        </form>
    <?php endif; ?>
<?php endif; ?>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
