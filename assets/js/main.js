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

  // Formulaires sensibles : <form data-confirmer="Message de confirmation"> demande une confirmation avant l'envoi
  document.querySelectorAll('form[data-confirmer]').forEach((form) => {
    form.addEventListener('submit', (ev) => {
      if (!window.confirm(form.dataset.confirmer)) ev.preventDefault();
    });
  });

  // Bouton « Afficher » d'un mot de passe : <button type="button" class="oeil" data-cible="id_du_champ">Afficher</button>
  document.querySelectorAll('.oeil[data-cible]').forEach((bouton) => {
    const champ = document.getElementById(bouton.dataset.cible);
    if (!champ) return;
    bouton.setAttribute('aria-pressed', 'false');
    bouton.addEventListener('click', () => {
      const montrer = champ.type === 'password';
      champ.type = montrer ? 'text' : 'password';
      bouton.textContent = montrer ? 'Masquer' : 'Afficher';
      bouton.setAttribute('aria-pressed', String(montrer));
      bouton.setAttribute('aria-label', (montrer ? 'Masquer' : 'Afficher') + ' le mot de passe');
    });
  });

  // Les messages de succès disparaissent après 5 secondes
  document.querySelectorAll('.alerte-succes').forEach((el) => setTimeout(() => el.remove(), 5000));
});
