// Équipe 5 : page Mes signatures (dut1/signatures.php).
// Les données arrivent du serveur dans l'attribut data-donnees, puis sont rafraîchies toutes les 30 secondes
// par l'API indiquée dans data-api (dut1/signatures_api.php). Format : voir dut1/signatures_api.md.

/* ===== Configuration ===== */
let perPage = 10;                // lignes par page (recalculé pour remplir l'écran, voir fitRows)
const MIN_ROWS = 5;              // minimum de lignes affichées par page
const ACTUALISATION_MS = 30000;  // actualisation automatique

const page = document.getElementById("signatures-dut1");
let donnees = JSON.parse(page.dataset.donnees);  // { nombre, rang, total_dut1, objectif, signataires: [...] }
let signatures = versListe(donnees.signataires);

/* Lien personnel que les signataires ouvrent pour signer : même adresse que le QR code du DUT1. */
const MY_LINK = new URL(page.dataset.lienSignature, location.origin).href;

/* Adapte les données de l'API au format utilisé par l'affichage. */
function versListe(signataires) {
  return signataires.map((s) => ({
    name: `${s.nom} ${s.prenom}`,
    role: s.type_parrain,
    date: s.date,          // "2026-10-20 14:32", du plus récent au plus ancien
    photo: s.photo,        // adresse de la photo ou null
    whatsapp: s.whatsapp,  // lien https://wa.me/… ou null (le numéro n'est jamais envoyé)
  }));
}

/* ===== État et outils ===== */
const state = { page: 1 };
const $ = (s) => document.querySelector(s);
const esc = (s) => String(s).replace(/[&<>"']/g, (c) => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c]));
const parse = (date) => { const [y, m, d] = date.slice(0, 10).split("-").map(Number); return new Date(y, m - 1, d); };
const fmtLong = (date) => parse(date).toLocaleDateString("fr-FR", { day: "2-digit", month: "short", year: "numeric" });
const fmtShort = (date) => parse(date).toLocaleDateString("fr-FR", { day: "2-digit", month: "short" });
const rangTexte = (rang) => (rang === 1 ? "1er" : `${rang}e`);

const WA_ICON = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3.5 20.5l1.3-4.2A8.5 8.5 0 1 1 8 19.3z"/><path d="M9 8.5c0 3.5 3 6.5 6.5 6.5l1.2-1.5-2-1-1 .8c-.8-.4-1.6-1.2-2-2l.8-1-1-2z"/></svg>`;

function avatarHTML(s, i) {
  if (s.photo) return `<img class="classement-avatar" src="${esc(s.photo)}" alt="">`;
  const initials = s.name.split(" ").slice(0, 2).map((w) => w[0]).join("").toUpperCase();
  return `<span class="classement-avatar" style="background:hsl(${(i * 47) % 360} 45% 32%)">${esc(initials)}</span>`;
}

function waHTML(s) {
  const ok = typeof s.whatsapp === "string" && /^https:\/\/wa\.me\/\d+$/.test(s.whatsapp);
  const label = `Écrire à ${s.name} sur WhatsApp`;
  return ok
    ? `<a class="signatures-dut1-wa" href="${esc(s.whatsapp)}" target="_blank" rel="noopener" aria-label="${esc(label)}">${WA_ICON}<span class="signatures-dut1-wa-text">WhatsApp</span></a>`
    : `<a class="signatures-dut1-wa" aria-disabled="true" role="link" title="Numéro non renseigné" aria-label="Numéro de ${esc(s.name)} non renseigné">${WA_ICON}<span class="signatures-dut1-wa-text">WhatsApp</span></a>`;
}

function rowHTML(s, i) {
  return `
    <div class="signatures-dut1-row">
      <span class="classement-person">${avatarHTML(s, i)}<span class="signatures-dut1-name">${esc(s.name)}</span></span>
      <span class="signatures-dut1-date">${fmtLong(s.date)}</span>
      ${waHTML(s)}
    </div>`;
}

/* ===== Rendu ===== */
function pageList(current, total) {
  const set = new Set([1, total, current - 1, current, current + 1]);
  const nums = [...set].filter((n) => n >= 1 && n <= total).sort((a, b) => a - b);
  const out = [];
  nums.forEach((n, i) => {
    if (i && n - nums[i - 1] > 1) out.push("…");
    out.push(n);
  });
  return out;
}

function renderPager(pages) {
  const pagerEl = $("#signatures-dut1-pages");
  if (pages <= 1) { pagerEl.innerHTML = ""; return; }
  let html = `<button type="button" data-p="${state.page - 1}" ${state.page === 1 ? "disabled" : ""} aria-label="Page précédente">‹</button>`;
  pageList(state.page, pages).forEach((n) => {
    html += n === "…"
      ? `<button type="button" disabled>…</button>`
      : `<button type="button" data-p="${n}" class="${n === state.page ? "classement-pager-active" : ""}" ${n === state.page ? 'aria-current="page"' : ""}>${n}</button>`;
  });
  html += `<button type="button" data-p="${state.page + 1}" ${state.page === pages ? "disabled" : ""} aria-label="Page suivante">›</button>`;
  pagerEl.innerHTML = html;
}

function renderTable() {
  const list = signatures;
  const pages = Math.max(1, Math.ceil(list.length / perPage));
  state.page = Math.min(state.page, pages);
  const start = (state.page - 1) * perPage;
  const slice = list.slice(start, start + perPage);

  $("#signatures-dut1-lignes").innerHTML = slice.length
    ? slice.map((s, i) => rowHTML(s, start + i)).join("")
    : `<p class="classement-empty">Tu n'as pas encore reçu de signature. Partage ton lien !</p>`;
  renderPager(pages);
  $("#signatures-dut1-total").textContent = `Total : ${list.length} signature${list.length > 1 ? "s" : ""} reçue${list.length > 1 ? "s" : ""}`;
}

function renderSummary() {
  const total = donnees.nombre;
  const pct = Math.min(100, Math.round((total / donnees.objectif) * 100));
  const latest = [...signatures].sort((a, b) => b.date.localeCompare(a.date))[0];

  $("#signatures-dut1-recues").textContent = total;
  $("#signatures-dut1-objectif").textContent = `${pct} %`;
  $("#signatures-dut1-objectif-libelle").textContent = `Objectif atteint (${total} / ${donnees.objectif})`;
  $("#signatures-dut1-rang").textContent = rangTexte(donnees.rang);
  $("#signatures-dut1-rang-libelle").textContent = `Rang sur ${donnees.total_dut1} DUT1`;
  $("#signatures-dut1-derniere").textContent = latest ? fmtShort(latest.date) : "–";
}

/* ===== Partage du lien (bouton du bandeau) ===== */
function setupShare() {
  const btn = $("#signatures-dut1-partager");
  const original = btn.innerHTML;
  let timer;

  async function copy() {
    try {
      await navigator.clipboard.writeText(MY_LINK);
    } catch {
      const t = document.createElement("textarea");
      t.value = MY_LINK;
      document.body.appendChild(t);
      t.select();
      document.execCommand("copy");
      t.remove();
    }
    btn.textContent = "Lien copié !";
    clearTimeout(timer);
    timer = setTimeout(() => (btn.innerHTML = original), 2000);
  }

  btn.addEventListener("click", async () => {
    if (navigator.share) {
      try {
        await navigator.share({ title: "Site Signature 2027", text: "Signe-moi pour Site Signature 2027 !", url: MY_LINK });
        return;
      } catch (e) { if (e.name === "AbortError") return; }
    }
    copy();
  });
}

/* ===== Actualisation automatique (toutes les 30 s) ===== */
let dernierEtat = JSON.stringify(donnees.signataires);

async function actualiser() {
  if (document.hidden) return; // onglet en arrière-plan : on attend le prochain tour
  const etatEl = $("#signatures-dut1-etat");
  try {
    const reponse = await fetch(page.dataset.api, {
      credentials: "same-origin",
      cache: "no-store",
      headers: { Accept: "application/json" },
    });
    if (reponse.status === 401) {
      clearInterval(minuteur);
      etatEl.textContent = "Ta session a expiré : recharge la page pour te reconnecter.";
      return;
    }
    if (!reponse.ok) throw new Error(`HTTP ${reponse.status}`);
    const nouvelles = await reponse.json();
    etatEl.textContent = "";
    donnees = nouvelles;
    renderSummary(); // le rang peut changer même sans nouvelle signature
    const etat = JSON.stringify(nouvelles.signataires);
    if (etat === dernierEtat) return; // aucune nouvelle signature : on ne redessine pas le tableau
    dernierEtat = etat;
    signatures = versListe(nouvelles.signataires);
    renderTable();
  } catch {
    etatEl.textContent = "Actualisation impossible pour le moment. Nouvel essai dans 30 secondes.";
  }
}

/* ===== Événements ===== */
$("#signatures-dut1-pages").addEventListener("click", (e) => {
  const btn = e.target.closest("button[data-p]");
  if (!btn || btn.disabled) return;
  state.page = Number(btn.dataset.p);
  renderTable();
});

document.addEventListener("click", (e) => {
  const a = e.target.closest('.signatures-dut1-wa[aria-disabled="true"]');
  if (a) e.preventDefault();
});

/* ===== Remplir toute la hauteur de l'écran =====
   Calcule combien de lignes tiennent entre le haut du tableau et le bas de l'écran
   (le pied de page commun, .pied, reste visible). */
function fitRows() {
  const row = document.querySelector(".signatures-dut1-row");
  if (!row) return;
  const rowsEl = $("#signatures-dut1-lignes");
  const rowH = row.getBoundingClientRect().height;
  const foot = page.querySelector(".classement-board-foot").offsetHeight;
  const pageFooter = (document.querySelector(".pied")?.offsetHeight ?? 0) + 30;
  const top = rowsEl.getBoundingClientRect().top + window.scrollY;
  const room = window.innerHeight - top - foot - pageFooter - 24;
  const n = Math.max(MIN_ROWS, Math.floor(room / rowH));
  if (n === perPage) return;
  const firstShown = (state.page - 1) * perPage; // garde la même première ligne visible
  perPage = n;
  state.page = Math.floor(firstShown / perPage) + 1;
  renderTable();
}

let resizeTimer;
window.addEventListener("resize", () => {
  clearTimeout(resizeTimer);
  resizeTimer = setTimeout(fitRows, 150);
});

/* ===== Démarrage ===== */
setupShare();
renderSummary();
renderTable();
fitRows();
const minuteur = setInterval(actualiser, ACTUALISATION_MS);
