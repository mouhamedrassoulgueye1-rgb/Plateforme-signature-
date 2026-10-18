<?php
/**
 * DUT1 – « Box » de signature. Le DUT1 tend son téléphone ; le parrain / la Mame saisit SES identifiants.
 *   succès / déjà signé → retour à l'accueil avec un message (MESSAGES_SIGNATURE)
 *   identifiants faux   → on reste sur la page avec l'erreur, pour réessayer
 * Le parrain n'est pas connecté pour autant : la session reste celle du DUT1.
 */
require_once __DIR__ . '/../includes/init.php';
$moi = exiger_role('dut1');

$erreur = '';
$nom_utilisateur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifier_csrf();
    $nom_utilisateur = normaliser_nom_utilisateur((string) ($_POST['nom_utilisateur'] ?? ''));
    $mot_de_passe    = (string) ($_POST['mot_de_passe'] ?? '');

    // 5 essais ratés puis 60 s d'attente (sinon un DUT1 pourrait deviner le mot de passe d'un parrain)
    $echecs = $_SESSION['signer_echecs'] ?? ['nb' => 0, 'depuis' => 0];
    if (time() - $echecs['depuis'] >= 60) {
        $echecs = ['nb' => 0, 'depuis' => 0];
    }

    if ($echecs['nb'] >= 5) {
        $erreur = 'Trop d’essais. Patientez une minute avant de réessayer.';
    } elseif ($nom_utilisateur === '' || $mot_de_passe === '') {
        $erreur = 'Renseignez le nom d’utilisateur et le mot de passe du parrain ou de la Mame.';
    } else {
        $parrain = verifier_identifiants($pdo, 'parrain', $nom_utilisateur, $mot_de_passe);
        if ($parrain === null) {
            $echecs['nb']++;
            $echecs['depuis'] = time();
            $_SESSION['signer_echecs'] = $echecs;
            $erreur = 'Nom d’utilisateur ou mot de passe incorrect, ou compte suspendu.';
        } else {
            unset($_SESSION['signer_echecs']);
            $resultat = enregistrer_signature($pdo, $parrain['id'], $moi['id']);
            flash($resultat === 'ok' ? 'succes' : 'erreur', MESSAGES_SIGNATURE[$resultat]);
            rediriger('dut1/accueil.php');
        }
    }
}

$titre_page = 'Se faire signer';
require __DIR__ . '/../includes/header.php';
?>
<section class="carte signer-carte">
    <h1>Se faire signer</h1>
    <p>Passez le téléphone à votre parrain ou à votre Mame : il ou elle saisit ses propres identifiants
       pour signer pour <strong><?= e($moi['prenom'] . ' ' . $moi['nom']) ?></strong>.</p>

    <?php if ($erreur !== ''): ?>
        <div class="alerte alerte-erreur" role="alert"><?= e($erreur) ?></div>
    <?php endif; ?>

    <form class="formulaire" method="post" autocomplete="off">
        <?= champ_csrf() ?>
        <div class="champ">
            <label for="nom_utilisateur">Nom d’utilisateur</label>
            <input id="nom_utilisateur" name="nom_utilisateur" required autocapitalize="none" value="<?= e($nom_utilisateur) ?>">
        </div>
        <div class="champ">
            <label for="mot_de_passe">Mot de passe</label>
            <input id="mot_de_passe" name="mot_de_passe" type="password" required autocomplete="off">
        </div>
        <button class="btn">Signer</button>
    </form>
    <p><a href="<?= e(url('dut1/accueil.php')) ?>">Retour à l’accueil</a></p>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
