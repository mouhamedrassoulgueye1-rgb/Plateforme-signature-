/* =====================================================
   Équipes 1-2 : pages espace/dut1.php et espace/parrain.php.
   Les liens (inscription, connexion) sont écrits par le PHP avec url() :
   ils fonctionnent en local (XAMPP) comme en production.
   ===================================================== */

// Active les animations d'entrée (le CSS les réserve aux navigateurs avec JavaScript)
document.documentElement.classList.add('js');

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
