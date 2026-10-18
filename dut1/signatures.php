<?php
require_once __DIR__ . '/../includes/init.php';
$moi = exiger_role('dut1');
require_once __DIR__ . '/signatures_fonctions.php';

// Équipe 5 : les signatures reçues par le DUT1 connecté, son nombre et son rang.
// Les données sont passées au JavaScript (assets/js/signatures_dut1.js) par data-donnees,
// puis rafraîchies toutes les 30 secondes par l'API signatures_api.php.
$donnees = lire_donnees_signatures($pdo, $moi['id']);

// Lien que le DUT1 partage aux signataires : la même adresse que son QR code (voir README).
$lien_signature = url('parrain/valider.php?dut1=' . $moi['id']);

$titre_page = 'Mes signatures';
$bandeau = ['titre' => $titre_page, 'texte' => 'Retrouve toutes les signatures reçues de tes parrains, marraines et Mames.'];
require __DIR__ . '/../includes/header.php';
?>
<div class="classement-page signatures-dut1" id="signatures-dut1"
     data-api="<?= e(url('dut1/signatures_api.php')) ?>"
     data-lien-signature="<?= e($lien_signature) ?>"
     data-donnees="<?= e(json_encode($donnees, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)) ?>">

    <nav class="classement-liens" aria-label="Classement et signatures">
        <a class="classement-lien" href="<?= e(url('dut1/classement.php')) ?>">Classement</a>
        <a class="classement-lien" href="<?= e(url('dut1/signatures.php')) ?>" aria-current="page">Mes signatures</a>
        <button class="signatures-dut1-partager" id="signatures-dut1-partager" type="button">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="6" cy="12" r="2.5"/><circle cx="17" cy="6" r="2.5"/><circle cx="17" cy="18" r="2.5"/><path d="M8.2 10.8l6.6-3.6M8.2 13.2l6.6 3.6"/></svg>
            Partager mon lien
        </button>
    </nav>

    <!-- Cartes de résumé -->
    <div class="signatures-dut1-kpis">
        <div class="signatures-dut1-kpi">
            <span class="signatures-dut1-kpi-icon signatures-dut1-kpi-icon-blue"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 20l1-4L17 4a2.1 2.1 0 0 1 3 3L8 19z"/><path d="M14 7l3 3"/></svg></span>
            <div><strong id="signatures-dut1-recues">–</strong><span>Signatures reçues</span></div>
        </div>
        <div class="signatures-dut1-kpi">
            <span class="signatures-dut1-kpi-icon signatures-dut1-kpi-icon-green"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="4.5"/><circle cx="12" cy="12" r="1"/></svg></span>
            <div><strong id="signatures-dut1-objectif">–</strong><span id="signatures-dut1-objectif-libelle">Objectif atteint</span></div>
        </div>
        <div class="signatures-dut1-kpi">
            <span class="signatures-dut1-kpi-icon signatures-dut1-kpi-icon-gold"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M8 21h8M12 17v4M7 4h10v5a5 5 0 0 1-10 0z"/><path d="M7 6H4v2a3 3 0 0 0 3 3M17 6h3v2a3 3 0 0 1-3 3"/></svg></span>
            <div><strong id="signatures-dut1-rang">–</strong><span id="signatures-dut1-rang-libelle">Rang au classement</span></div>
        </div>
        <div class="signatures-dut1-kpi">
            <span class="signatures-dut1-kpi-icon signatures-dut1-kpi-icon-purple"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></span>
            <div><strong id="signatures-dut1-derniere">–</strong><span>Dernière signature</span></div>
        </div>
    </div>

    <!-- Tableau des signatures -->
    <div class="signatures-dut1-layout">
        <section class="classement-board">
            <div class="signatures-dut1-head">
                <span>Signataire</span><span>Date de signature</span><span>WhatsApp</span>
            </div>
            <div class="signatures-dut1-rows" id="signatures-dut1-lignes"></div>
            <div class="classement-board-foot">
                <nav class="classement-pager" id="signatures-dut1-pages" aria-label="Pagination"></nav>
                <span class="classement-total" id="signatures-dut1-total"></span>
            </div>
        </section>
    </div>

    <p class="petit" id="signatures-dut1-etat" aria-live="polite"></p>
</div>
<script src="<?= e(url('assets/js/signatures_dut1.js')) ?>"></script>
<?php require __DIR__ . '/../includes/footer.php'; ?>
