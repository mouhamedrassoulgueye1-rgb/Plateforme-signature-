<?php
/**
 * Tout ce qui touche aux signatures. Les deux chemins de signature (box sur le téléphone du DUT1,
 * Équipe 3, et scan du QR code, Équipe 4) passent par enregistrer_signature() : jamais d'INSERT ailleurs.
 */

/**
 * Enregistre la signature d'un parrain pour un DUT1.
 * Renvoie 'ok', 'deja_signe' (le parrain a déjà signé pour ce DUT1), 'introuvable' (id inconnu)
 * ou 'suspendu' (l'un des deux comptes a été suspendu par l'admin).
 */
function enregistrer_signature(PDO $pdo, int $id_parrain, int $id_filleul): string
{
    $req = $pdo->prepare(
        'SELECT (SELECT statut_compte FROM parrain WHERE id_parrain = ?) AS p,
                (SELECT statut_compte FROM filleul WHERE id_filleul = ?) AS f'
    );
    $req->execute([$id_parrain, $id_filleul]);
    $statuts = $req->fetch();
    if ($statuts['p'] === null || $statuts['f'] === null) {
        return 'introuvable';
    }
    if ($statuts['p'] !== 'actif' || $statuts['f'] !== 'actif') {
        return 'suspendu';
    }
    try {
        $req = $pdo->prepare('INSERT INTO signature (id_parrain, id_filleul) VALUES (?, ?)');
        $req->execute([$id_parrain, $id_filleul]);
        return 'ok';
    } catch (PDOException $ex) {
        $code = (int) ($ex->errorInfo[1] ?? 0);
        if ($code === 1062) {
            return 'deja_signe';
        }
        if ($code === 1452) {
            return 'introuvable';
        }
        throw $ex;
    }
}

/** Messages à afficher selon le résultat de enregistrer_signature() (mêmes textes partout). */
const MESSAGES_SIGNATURE = [
    'ok'          => 'Signature enregistrée. Merci !',
    'deja_signe'  => 'Vous avez déjà signé pour cette personne.',
    'introuvable' => 'Ce DUT1 est introuvable.',
    'suspendu'    => 'Signature impossible : ce compte est suspendu.',
];

function nb_signatures_filleul(PDO $pdo, int $id_filleul): int
{
    $req = $pdo->prepare('SELECT COUNT(*) FROM signature WHERE id_filleul = ?');
    $req->execute([$id_filleul]);
    return (int) $req->fetchColumn();
}

function nb_signatures_parrain(PDO $pdo, int $id_parrain): int
{
    $req = $pdo->prepare('SELECT COUNT(*) FROM signature WHERE id_parrain = ?');
    $req->execute([$id_parrain]);
    return (int) $req->fetchColumn();
}

/** Personnes qui ont signé pour un DUT1 (pour la liste, le lien WhatsApp et le téléchargement). */
function signataires_du_filleul(PDO $pdo, int $id_filleul): array
{
    $req = $pdo->prepare(
        'SELECT p.id_parrain, p.prenom, p.nom, p.type_parrain, p.chambre, p.telephone, s.date_signature
         FROM signature s JOIN parrain p ON p.id_parrain = s.id_parrain
         WHERE s.id_filleul = ? ORDER BY s.date_signature'
    );
    $req->execute([$id_filleul]);
    return $req->fetchAll();
}

/** DUT1 pour qui un parrain a signé. */
function filleuls_signes_par(PDO $pdo, int $id_parrain): array
{
    $req = $pdo->prepare(
        'SELECT f.id_filleul, f.prenom, f.nom, f.telephone, s.date_signature
         FROM signature s JOIN filleul f ON f.id_filleul = s.id_filleul
         WHERE s.id_parrain = ? ORDER BY s.date_signature'
    );
    $req->execute([$id_parrain]);
    return $req->fetchAll();
}

/** Rang d'un DUT1 : 1 + nombre de DUT1 qui ont strictement plus de signatures (ex aequo = même rang). */
function rang_filleul(PDO $pdo, int $id_filleul): int
{
    $nb = nb_signatures_filleul($pdo, $id_filleul);
    $req = $pdo->prepare(
        'SELECT COUNT(*) FROM (
            SELECT f.id_filleul FROM filleul f LEFT JOIN signature s ON s.id_filleul = f.id_filleul
            WHERE f.statut_compte = \'actif\'
            GROUP BY f.id_filleul HAVING COUNT(s.id_parrain) > ?
         ) AS devant'
    );
    $req->execute([$nb]);
    return 1 + (int) $req->fetchColumn();
}

/** Nombre total de DUT1 actifs (pour afficher « 3e sur 120 »). */
function nb_filleuls_actifs(PDO $pdo): int
{
    return (int) $pdo->query('SELECT COUNT(*) FROM filleul WHERE statut_compte = \'actif\'')->fetchColumn();
}
