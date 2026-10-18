/* =========================================================
   Équipes 1-2 : pages de connexion (connexion/dut1.php et connexion/parrain.php).
   Le formulaire est envoyé normalement au serveur (PHP vérifie tout) ; ce script ajoute
   seulement le bouton « Afficher », la vérification des champs vides et l'indicateur d'envoi.
   ========================================================= */
const $ = (id) => document.getElementById(id);

const champUtilisateur = $('nom_utilisateur');
const champMdp = $('mot_de_passe');
const oeil = $('eye');

/* Afficher / cacher le mot de passe */
oeil.addEventListener('click', () => {
  const montrer = champMdp.type === 'password';
  champMdp.type = montrer ? 'text' : 'password';
  oeil.textContent = montrer ? 'Masquer' : 'Afficher';
  oeil.setAttribute('aria-pressed', String(montrer));
  oeil.setAttribute('aria-label', (montrer ? 'Masquer' : 'Afficher') + ' le mot de passe');
});

function dire(texte) { $('msg').textContent = texte; }
function marquer(champ, invalide) { champ.setAttribute('aria-invalid', invalide ? 'true' : 'false'); }

[champUtilisateur, champMdp].forEach((champ) => champ.addEventListener('input', () => { marquer(champ, false); dire(''); }));

$('form').addEventListener('submit', (ev) => {
  const nom = champUtilisateur.value.trim();
  const mdp = champMdp.value;
  marquer(champUtilisateur, !nom);
  marquer(champMdp, !mdp);
  if (!nom || !mdp) {
    ev.preventDefault();
    dire(!nom ? 'Saisis ton nom d\'utilisateur.' : 'Saisis ton mot de passe.');
    (!nom ? champUtilisateur : champMdp).focus();
    return;
  }
  $('go').disabled = true;
  $('goText').textContent = 'Connexion en cours';
});
