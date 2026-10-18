<?php
/**
 * Équipe 5 : données des pages « Mes signatures » (dut1/signatures.php) et « Classement » (dut1/classement.php),
 * et outils de leurs API JSON (dut1/signatures_api.php, dut1/classement_api.php).
 *
 * Lecture seule : les signatures sont enregistrées ailleurs, par enregistrer_signature().
 * Le nombre, le rang, le total et la liste des signataires viennent des fonctions communes de
 * includes/signatures.php : on les appelle, on ne les réécrit pas (CONTRIBUTING.md, §5).
 */

/** Objectif de signatures d'un DUT1 : 100 % de progression sur les deux pages. */
const OBJECTIF_SIGNATURES = 120;

// ---------------------------------------------------------------------------------------------
// Données des pages
// ---------------------------------------------------------------------------------------------

/**
 * Page « Mes signatures » : nombre, rang et signataires du DUT1, au format de dut1/signatures_exemple.json.
 * La page s'en sert au premier affichage et l'API le renvoie toutes les 30 secondes.
 */
function lire_donnees_signatures(PDO $pdo, int $id_filleul): array
{
    $signataires = [];
    // signataires_du_filleul() trie du plus ancien au plus récent : on inverse pour avoir le dernier en haut.
    foreach (array_reverse(signataires_du_filleul($pdo, $id_filleul)) as $p) {
        $signataires[] = [
            'nom'          => $p['nom'],
            'prenom'       => $p['prenom'],
            'type_parrain' => $p['type_parrain'],
            'chambre'      => $p['chambre'],
            // Chemin complet (avec BASE_URL) pour que l'image s'affiche quelle que soit la page
            'photo'        => !empty($p['photo']) ? url($p['photo']) : null,
            // Le numéro brut n'est jamais envoyé au navigateur : seulement le lien WhatsApp
            'whatsapp'     => lien_whatsapp_ou_null($p['telephone']),
            // « 2026-10-20 14:32:00 » (DATETIME de la base) → « 2026-10-20 14:32 », sans conversion d'heure
            'date'         => substr($p['date_signature'], 0, 16),
        ];
    }

    return [
        'nombre'      => nb_signatures_filleul($pdo, $id_filleul),
        'rang'        => rang_filleul($pdo, $id_filleul),
        'total_dut1'  => nb_filleuls_actifs($pdo),
        'objectif'    => OBJECTIF_SIGNATURES,
        'mis_a_jour'  => heure_dakar(),
        'signataires' => $signataires,
    ];
}

/**
 * Page « Classement » : tous les DUT1 actifs, du plus signé au moins signé, et les statistiques globales.
 * $id_filleul sert seulement à repérer le DUT1 connecté (« moi ») ; aucun id n'est envoyé au navigateur.
 */
function lire_classement(PDO $pdo, int $id_filleul): array
{
    $req = $pdo->prepare(
        "SELECT f.id_filleul, f.nom, f.prenom, f.photo, COUNT(s.id_parrain) AS nb_signatures
         FROM filleul f LEFT JOIN signature s ON s.id_filleul = f.id_filleul
         WHERE f.statut_compte = 'actif'
         GROUP BY f.id_filleul, f.nom, f.prenom, f.photo
         ORDER BY nb_signatures DESC, f.nom, f.prenom"
    );
    $req->execute();

    $classement = [];
    $rang = 0;
    $precedent = null;
    foreach ($req->fetchAll() as $i => $l) {
        $nb = (int) $l['nb_signatures'];
        // Même règle que rang_filleul() : les ex æquo ont le même rang (10, 8, 8, 5 → 1, 2, 2, 4)
        if ($nb !== $precedent) {
            $rang = $i + 1;
            $precedent = $nb;
        }
        $classement[] = [
            'rang'          => $rang,
            'nom'           => $l['nom'],
            'prenom'        => $l['prenom'],
            'photo'         => !empty($l['photo']) ? url($l['photo']) : null,
            'nb_signatures' => $nb,
            'moi'           => (int) $l['id_filleul'] === $id_filleul,
        ];
    }

    $nombres = array_column($classement, 'nb_signatures');
    return [
        'objectif'     => OBJECTIF_SIGNATURES,
        'mis_a_jour'   => heure_dakar(),
        'statistiques' => [
            'inscrits'         => count($classement),
            'signatures'       => array_sum($nombres),
            'meilleur'         => $nombres ? max($nombres) : 0,
            'objectif_atteint' => count(array_filter($nombres, fn($n) => $n >= OBJECTIF_SIGNATURES)),
        ],
        'classement'   => $classement,
    ];
}

/**
 * Heure de la réponse (« mis_a_jour »), à l'heure du Sénégal : le php.ini de XAMPP est réglé sur Europe/Berlin.
 * Le fuseau est donné ici, sans date_default_timezone_set(), pour ne pas changer l'heure des autres pages.
 */
function heure_dakar(): string
{
    return (new DateTime('now', new DateTimeZone('Africa/Dakar')))->format('Y-m-d\TH:i:s');
}

/** Lien WhatsApp d'un signataire, ou null si son numéro est inutilisable (le front n'affiche alors pas de bouton). */
function lien_whatsapp_ou_null(?string $telephone): ?string
{
    $numero = normaliser_telephone((string) $telephone);
    return $numero === null ? null : lien_whatsapp($numero);
}

/** Vrai si le DUT1 existe toujours et n'a pas été suspendu par l'admin depuis sa connexion. */
function compte_dut1_actif(PDO $pdo, int $id_filleul): bool
{
    $req = $pdo->prepare('SELECT statut_compte FROM filleul WHERE id_filleul = ?');
    $req->execute([$id_filleul]);
    return $req->fetchColumn() === 'actif';
}

// ---------------------------------------------------------------------------------------------
// Outils des API JSON (toujours du JSON, même en cas d'erreur)
// ---------------------------------------------------------------------------------------------

/** Envoie une réponse JSON avec son code HTTP, puis arrête le script. */
function repondre_json(int $code, array $contenu): void
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($contenu, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    exit;
}

/** 405 si la méthode n'est pas GET : les API de l'équipe 5 ne font que lire. */
function exiger_get(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        header('Allow: GET');
        repondre_json(405, ['erreur' => 'Méthode non autorisée']);
    }
}

/**
 * Renvoie le DUT1 connecté, ou répond 401 (pas connecté en DUT1, compte suspendu ou supprimé).
 * Pas d'exiger_role() dans une API : il redirige vers la page de connexion, alors que le JavaScript attend du JSON.
 */
function exiger_dut1_api(PDO $pdo): array
{
    $moi = utilisateur();
    if ($moi === null || $moi['role'] !== 'dut1' || !compte_dut1_actif($pdo, $moi['id'])) {
        repondre_json(401, ['erreur' => 'Authentification requise']);
    }
    return $moi;
}

/** 500 sans détail technique : le message va dans le journal PHP, jamais dans la réponse. */
function repondre_erreur_serveur(Throwable $ex): void
{
    error_log(($_SERVER['SCRIPT_NAME'] ?? 'API') . ' : ' . $ex->getMessage());
    repondre_json(500, ['erreur' => 'Erreur serveur']);
}
