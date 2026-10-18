<?php
require_once __DIR__ . '/../includes/init.php';
$moi = exiger_role('dut1');
require_once __DIR__ . '/signatures_fonctions.php';

// Équipe 5 : classement de tous les DUT1 actifs selon le nombre de signatures reçues.
// Les données sont passées au JavaScript (assets/js/classement.js) par data-donnees,
// puis rafraîchies toutes les 30 secondes par l'API classement_api.php.
$donnees = lire_classement($pdo, $moi['id']);

$titre_page = 'Classement';
$bandeau = ['titre' => $titre_page, 'texte' => 'Découvre le classement des DUT1 selon le nombre de signatures reçues.'];
require __DIR__ . '/../includes/header.php';
?>
<div class="classement-page" id="classement"
     data-api="<?= e(url('dut1/classement_api.php')) ?>"
     data-donnees="<?= e(json_encode($donnees, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)) ?>">

    <nav class="classement-liens" aria-label="Classement et signatures">
        <a class="classement-lien" href="<?= e(url('dut1/classement.php')) ?>" aria-current="page">Classement</a>
        <a class="classement-lien" href="<?= e(url('dut1/signatures.php')) ?>">Mes signatures</a>
    </nav>

    <div class="classement-grid">

        <!-- Onglets -->
        <div class="classement-tabs" role="tablist">
            <button class="classement-tab classement-tab-active" type="button" role="tab" aria-selected="true" data-tab="general">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M8 21h8M12 17v4M7 4h10v5a5 5 0 0 1-10 0z"/><path d="M7 6H4v2a3 3 0 0 0 3 3M17 6h3v2a3 3 0 0 1-3 3"/></svg>
                Classement général
            </button>
            <span class="classement-tab-sep"></span>
            <button class="classement-tab" type="button" role="tab" aria-selected="false" data-tab="results">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.6-7 8-7s8 3 8 7"/></svg>
                Mes résultats
            </button>
        </div>

        <!-- Recherche -->
        <div class="classement-search">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><circle cx="11" cy="11" r="6.5"/><path d="M16 16l4.5 4.5"/></svg>
            <input id="classement-recherche" type="search" placeholder="Rechercher un DUT1..." autocomplete="off" aria-label="Rechercher un DUT1">
        </div>

        <!-- Tableau -->
        <section class="classement-board">
            <div class="classement-board-head">
                <span>Rang</span><span>DUT1</span><span>Signatures</span><span>Progression</span>
            </div>
            <div id="classement-lignes" class="classement-rows"></div>
            <div class="classement-board-foot">
                <nav class="classement-pager" id="classement-pages" aria-label="Pagination"></nav>
                <span class="classement-total" id="classement-total"></span>
            </div>
        </section>

        <!-- Statistiques -->
        <aside class="classement-stats">
            <h2>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9.5"/><path d="M8 16v-4M12 16V8M16 16v-6"/></svg>
                Statistiques globales
            </h2>
            <div class="classement-stat-grid">
                <div class="classement-stat classement-stat-blue">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="8" r="3.5"/><path d="M2.5 20c0-3.5 3-6 6.5-6s6.5 2.5 6.5 6"/><circle cx="17" cy="9" r="2.5"/><path d="M17 14c2.8 0 4.5 2 4.5 5"/></svg>
                    <strong id="classement-st-inscrits">–</strong><span>DUT1 inscrits</span>
                </div>
                <div class="classement-stat classement-stat-green">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 20l1-4L17 4a2.1 2.1 0 0 1 3 3L8 19z"/><path d="M14 7l3 3"/></svg>
                    <strong id="classement-st-total">–</strong><span>Signatures totales</span>
                </div>
                <div class="classement-stat classement-stat-purple">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 17l6-6 4 4 8-8"/><path d="M15 7h6v6"/></svg>
                    <strong id="classement-st-meilleur">–</strong><span>Meilleur score</span>
                </div>
                <div class="classement-stat classement-stat-gold">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"><path d="M12 3l2.7 5.6 6.1.9-4.4 4.3 1 6.1L12 17l-5.4 2.9 1-6.1L3.2 9.5l6.1-.9z"/></svg>
                    <strong id="classement-st-complets">–</strong><span>DUT1 avec 100%</span>
                </div>
            </div>

            <div class="classement-stats-art" aria-hidden="true">
                <div class="classement-art-laptop"><span>&lt;/&gt;</span></div>
            </div>
            <p class="classement-stats-quote">« Chaque signature<br>est une connexion<br>vers l’avenir. »</p>
        </aside>
    </div>

    <p class="petit" id="classement-etat" aria-live="polite"></p>
</div>
<script src="<?= e(url('assets/js/classement.js')) ?>"></script>
<?php require __DIR__ . '/../includes/footer.php'; ?>
