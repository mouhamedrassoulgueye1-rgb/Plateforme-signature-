/* =========================================================
   Équipes 1-2 : pages de connexion (connexion/dut1.php et connexion/parrain.php).
   Le formulaire est envoyé normalement au serveur (PHP vérifie tout) ; ce script ajoute
   seulement la vérification des champs vides et l'indicateur d'envoi.
   Le bouton « Afficher » du mot de passe est géré par main.js.
   ========================================================= */
const formulaireConnexion = document.getElementById('form-connexion');
const champUtilisateur = document.getElementById('nom_utilisateur');
const champMdp = document.getElementById('mot_de_passe');
const zoneMessage = document.getElementById('connexion-message');

function marquer(champ, invalide) { champ.setAttribute('aria-invalid', invalide ? 'true' : 'false'); }

[champUtilisateur, champMdp].forEach((champ) => champ.addEventListener('input', () => {
  marquer(champ, false);
  zoneMessage.textContent = '';
}));

formulaireConnexion.addEventListener('submit', (ev) => {
  const nom = champUtilisateur.value.trim();
  const mdp = champMdp.value;
  marquer(champUtilisateur, !nom);
  marquer(champMdp, !mdp);
  if (!nom || !mdp) {
    ev.preventDefault();
    zoneMessage.textContent = !nom ? 'Saisis ton nom d’utilisateur.' : 'Saisis ton mot de passe.';
    (!nom ? champUtilisateur : champMdp).focus();
    return;
  }
  document.getElementById('connexion-bouton').disabled = true;
  document.getElementById('connexion-bouton-texte').textContent = 'Connexion en cours';
});
