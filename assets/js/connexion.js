/* =========================================================
   Page de connexion : Connexion DUT1
   Cette page est autonome : elle ne contient aucun lien ni aucun code
   des deux autres pages de connexion.
   ========================================================= */
const DEMO_MODE = true;           // true = pas de serveur : accepte tout identifiant non vide (pour tester)
                                  // À METTRE SUR false AVANT LA MISE EN LIGNE
const API_LOGIN = 'connexion/login-dut1.php';   // script PHP de ce profil (relatif à cette page)
const ROLE      = 'dut1';
const REDIRECT  = 'dut1/accueil.php';   // page ouverte après une connexion réussie

const $ = id => document.getElementById(id);

/* Afficher / cacher le mot de passe */
$('eye').addEventListener('click', () => {
  const show = $('pass').type === 'password';
  $('pass').type = show ? 'text' : 'password';
  $('eye').textContent = show ? 'Masquer' : 'Afficher';
  $('eye').setAttribute('aria-pressed', show);
  $('eye').setAttribute('aria-label', (show ? 'Masquer' : 'Afficher') + ' le mot de passe');
});

function say(text, ok){ const m = $('msg'); m.textContent = text; m.classList.toggle('ok', !!ok); }
function invalid(el, bad){ el.setAttribute('aria-invalid', bad ? 'true' : 'false'); }

['user','pass'].forEach(id => $(id).addEventListener('input', () => { invalid($(id), false); say(''); }));

$('form').addEventListener('submit', async e => {
  e.preventDefault();
  const username = $('user').value.trim(), password = $('pass').value;

  invalid($('user'), !username); invalid($('pass'), !password);
  if (!username || !password) {
    say(!username ? 'Saisis ton nom d\'utilisateur.' : 'Saisis ton mot de passe.');
    (!username ? $('user') : $('pass')).focus();
    return;
  }

  $('go').disabled = true; $('goText').textContent = 'Connexion en cours';
  say('');
  try {
    let data;
    if (DEMO_MODE) {
      await new Promise(r => setTimeout(r, 700));
      data = { ok:true };
    } else {
      const res = await fetch(API_LOGIN, {
        method:'POST',
        headers:{'Content-Type':'application/json'},
        credentials:'same-origin',
        body: JSON.stringify({ username, password, role: ROLE })
      });
      data = await res.json();
    }
    if (data.ok) {
      if (DEMO_MODE) {
        say('Connexion réussie. En ligne, tu serais redirigé vers ' + REDIRECT, true);
        $('go').disabled = false; $('goText').textContent = 'Se connecter';
      } else {
        say('Connexion réussie. Redirection en cours.', true);
        setTimeout(() => { location.href = REDIRECT; }, 600);
      }
    } else {
      throw new Error(data.error || 'Nom d\'utilisateur ou mot de passe incorrect.');
    }
  } catch (err) {
    invalid($('user'), true); invalid($('pass'), true);
    say(err.message && err.message !== 'Failed to fetch' ? err.message : 'Connexion impossible. Veuillez verifier les informations saisies');
    $('go').disabled = false; $('goText').textContent = 'Se connecter';
    $('pass').select();
  }
});