// Script commun, chargé sur toutes les pages par includes/footer.php.
// Le code propre à une page va dans son propre fichier (ex. assets/js/qrcode.js).
document.addEventListener('DOMContentLoaded', () => {
  // Formulaires avec deux champs de mot de passe : name="mot_de_passe" et name="confirmation_mot_de_passe"
  document.querySelectorAll('form').forEach((form) => {
    const mdp = form.querySelector('[name="mot_de_passe"]');
    const conf = form.querySelector('[name="confirmation_mot_de_passe"]');
    if (!mdp || !conf) return;
    const verifier = () => conf.setCustomValidity(conf.value === mdp.value ? '' : 'Les mots de passe ne correspondent pas.');
    mdp.addEventListener('input', verifier);
    conf.addEventListener('input', verifier);
  });

  // Les messages de succès disparaissent après 5 secondes
  document.querySelectorAll('.alerte-succes').forEach((el) => setTimeout(() => el.remove(), 5000));
});
