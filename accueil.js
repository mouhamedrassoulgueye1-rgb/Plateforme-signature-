/* =====================================================
   CONFIG : adapte les adresses à ton site
   Les deux pages d'accueil (accueil-dut1.html et accueil-parrain.html)
   utilisent ce même fichier : le profil est lu dans <body data-role="...">
   ===================================================== */
const ROUTES = {
  dut1: {
    signup: 'inscription-dut1.html',
    login:  'connexion-dut1.html'
  },
  parrain: {
    signup: 'inscription-parrain.html',
    login:  'connexion-parrain.html'
  }
};

const role = document.body.dataset.role;
const r = ROUTES[role];
if (r) {
  const set = (id, href) => { const el = document.getElementById(id); if (el) el.href = href; };
  set('goSignup', r.signup);
  set('goLogin',  r.login);
  set('mSignup',  r.signup);
  set('mLogin',   r.login);
}

/* =====================================================
   Menu (bouton hamburger)
   ===================================================== */
const burger = document.getElementById('burger');
const menu   = document.getElementById('menu');

function toggleMenu(open){
  const show = typeof open === 'boolean' ? open : menu.hidden;
  menu.hidden = !show;
  burger.setAttribute('aria-expanded', show);
  burger.setAttribute('aria-label', show ? 'Fermer le menu' : 'Ouvrir le menu');
}
burger.addEventListener('click', e => { e.stopPropagation(); toggleMenu(); });
document.addEventListener('click', e => { if (!menu.hidden && !menu.contains(e.target)) toggleMenu(false); });
document.addEventListener('keydown', e => { if (e.key === 'Escape') toggleMenu(false); });

/* =====================================================
   Animation d'entrée du bandeau (une seule fois)
   ===================================================== */
requestAnimationFrame(() => requestAnimationFrame(() => document.body.classList.add('ready')));