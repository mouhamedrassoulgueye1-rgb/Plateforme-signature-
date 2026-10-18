<?php
/**
 * PARRAIN / MAME – Les DUT1 pour lesquels j'ai signé.
 *   signatures.php            → la liste (chaque ligne mène à la fiche)
 *   signatures.php?dut1=12    → fiche d'un DUT1 (réservée à ceux pour qui CE signataire a signé)
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

$fiche_demandee = isset($_GET['dut1']);
$fiche = null;
$liste = [];

if ($fiche_demandee) {
    $req = $pdo->prepare(
        'SELECT f.id_filleul, f.prenom, f.nom, f.telephone, f.photo, s.date_signature,
                (SELECT COUNT(*) FROM signature WHERE id_filleul = f.id_filleul) AS nb_recues
         FROM signature s JOIN filleul f ON f.id_filleul = s.id_filleul
         WHERE s.id_parrain = ? AND f.id_filleul = ?'
    );
    $req->execute([$moi['id'], (int) $_GET['dut1']]);
    $fiche = $req->fetch() ?: null;
    if ($fiche === null) {
        http_response_code(404);
    }
} else {
    $req = $pdo->prepare(
        'SELECT f.id_filleul, f.prenom, f.nom, f.photo, s.date_signature
         FROM signature s JOIN filleul f ON f.id_filleul = s.id_filleul
         WHERE s.id_parrain = ? ORDER BY s.date_signature DESC'
    );
    $req->execute([$moi['id']]);
    $liste = $req->fetchAll();
}
$n = count($liste);

$titre_page = $fiche_demandee ? 'Fiche du DUT1' : 'Mes signatures';
require __DIR__ . '/../includes/header.php';
?>
<?php if ($fiche_demandee): ?>
<section class="carte signatures-parrain-fiche">
    <?php if ($fiche === null): ?>
        <h1>Fiche introuvable</h1>
        <div class="alerte alerte-erreur" role="alert">Vous n’avez pas signé pour cette personne, ou elle n’existe pas.</div>
    <?php else: ?>
        <h1>Fiche du DUT1</h1>
        <p><?= html_avatar($fiche, 96) ?></p>
        <p><strong><?= e($fiche['prenom'] . ' ' . $fiche['nom']) ?></strong></p>
        <dl>
            <dt>Prénom</dt><dd><?= e($fiche['prenom']) ?></dd>
            <dt>Nom</dt><dd><?= e($fiche['nom']) ?></dd>
            <?php if ((string) $fiche['telephone'] !== ''): ?>
                <dt>Téléphone</dt>
                <dd><?= e('+' . $fiche['telephone']) ?> · <a href="<?= e(lien_whatsapp($fiche['telephone'])) ?>" target="_blank" rel="noopener">WhatsApp</a></dd>
            <?php endif; ?>
            <dt>Vous avez signé le</dt><dd><?= e((new DateTime($fiche['date_signature']))->format('d/m/Y H:i')) ?></dd>
            <dt>Signatures reçues</dt><dd><?= e($fiche['nb_recues']) ?></dd>
        </dl>
    <?php endif; ?>
    <p><a href="<?= e(url('parrain/signatures.php')) ?>">Retour à mes signatures</a></p>
</section>
<?php else: ?>
<section class="carte signatures-parrain-liste">
    <h1>Mes signatures</h1>
    <p><?= e($n) ?> <?= $n > 1 ? 'DUT1 signés' : 'DUT1 signé' ?>
       <?php if ($n > 0): ?>· <a href="<?= e(url('parrain/telecharger.php')) ?>">Télécharger (CSV)</a><?php endif; ?></p>
    <?php if ($liste): ?>
        <ul class="signataire-liste">
            <?php foreach ($liste as $f): ?>
                <li class="signataire-ligne">
                    <?= html_avatar($f) ?>
                    <a href="<?= e(url('parrain/signatures.php?dut1=' . (int) $f['id_filleul'])) ?>"><strong><?= e($f['prenom'] . ' ' . $f['nom']) ?></strong></a>
                    <small>Signé le <?= e((new DateTime($f['date_signature']))->format('d/m/Y H:i')) ?></small>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>Vous n’avez encore signé pour aucun DUT1.</p>
    <?php endif; ?>
</section>
<?php endif; ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>
