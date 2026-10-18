// Équipe 5 : page Classement (dut1/classement.php).
// Les données arrivent du serveur dans l'attribut data-donnees, puis sont rafraîchies toutes les 30 secondes
// par l'API indiquée dans data-api (dut1/classement_api.php). Format : voir dut1/signatures_api.md.

/* ===== Configuration ===== */
const PER_PAGE = 10;             // lignes par page
const ACTUALISATION_MS = 30000;  // actualisation automatique

const page = document.getElementById("classement");
let donnees = JSON.parse(page.dataset.donnees);  // { objectif, statistiques, classement: [...] }
let dut1 = versListe(donnees.classement);

/* Adapte les données de l'API au format utilisé par l'affichage. */
function versListe(classement) {
  return classement.map((p) => ({
    rank: p.rang,
    name: `${p.nom} ${p.prenom}`,
    signatures: p.nb_signatures,
    photo: p.photo,
    me: p.moi,
  }));
}

/* ===== État ===== */
const state = { tab: "general", page: 1, query: "" };

/* ===== Outils ===== */
const $ = (sel) => document.querySelector(sel);
const rowsEl = $("#classement-lignes");
const pagerEl = $("#classement-pages");
const totalEl = $("#classement-total");
const etatEl = $("#classement-etat");
const percent = (n) => Math.min(100, Math.round((n / donnees.objectif) * 100));
const fmt = (n) => n.toLocaleString("fr-FR");
const esc = (s) => String(s).replace(/[&<>"']/g, (c) => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c]));
const normalize = (s) => s.normalize("NFD").replace(/[̀-ͯ]/g, "").toLowerCase();

function avatarHTML(p) {
  if (p.photo) return `<img class="classement-avatar" src="${esc(p.photo)}" alt="">`;
  const initials = p.name.split(" ").slice(0, 2).map((w) => w[0]).join("").toUpperCase();
  const hue = (p.rank * 47) % 360;
  return `<span class="classement-avatar" style="background:hsl(${hue} 45% 32%)">${esc(initials)}</span>`;
}

function rowHTML(p) {
  const pct = percent(p.signatures);
  const cls = ["classement-row", p.rank === 1 ? "classement-row-first" : "", p.me ? "classement-row-me" : ""].join(" ").trim();
  const medal = p.rank <= 3 ? ` classement-rank-${p.rank}` : "";
  return `
    <div class="${cls}">
      <span><span class="classement-rank${medal}">${p.rank}</span></span>
      <span class="classement-person">${avatarHTML(p)}<span class="classement-person-name">${esc(p.name)}</span></span>
      <span class="classement-sig">${p.signatures}</span>
      <span class="classement-progress">
        <span class="classement-bar"><i style="width:${pct}%"></i></span>
        <span class="classement-pct">${pct}%</span>
      </span>
    </div>`;
}

function filtered() {
  const q = normalize(state.query.trim());
  return q ? dut1.filter((p) => normalize(p.name).includes(q)) : dut1;
}

/* ===== Rendu ===== */
function renderPager(pages) {
  if (pages <= 1) { pagerEl.innerHTML = ""; return; }
  let html = `<button type="button" data-p="${state.page - 1}" ${state.page === 1 ? "disabled" : ""} aria-label="Page précédente">‹</button>`;
  for (let i = 1; i <= pages; i++) {
    html += `<button type="button" data-p="${i}" class="${i === state.page ? "classement-pager-active" : ""}"${i === state.page ? 'aria-current="page"' : ""}>${i}</button>`;
  }
  html += `<button type="button" data-p="${state.page + 1}" ${state.page === pages ? "disabled" : ""} aria-label="Page suivante">›</button>`;
  pagerEl.innerHTML = html;
}

function renderGeneral() {
  const list = filtered();
  const pages = Math.max(1, Math.ceil(list.length / PER_PAGE));
  state.page = Math.min(state.page, pages);
  const slice = list.slice((state.page - 1) * PER_PAGE, state.page * PER_PAGE);

  rowsEl.innerHTML = slice.length
    ? slice.map(rowHTML).join("")
    : `<p class="classement-empty">${state.query.trim() ? `Aucun DUT1 ne correspond à « ${esc(state.query)} ».` : "Aucun DUT1 inscrit pour l’instant."}</p>`;
  renderPager(pages);
  totalEl.textContent = `Total : ${list.length} DUT1 inscrits`;
}

function renderResults() {
  const me = dut1.find((p) => p.me);
  if (!me) {
    rowsEl.innerHTML = `<p class="classement-empty">Tes résultats apparaîtront ici dès que tu auras reçu une signature.</p>`;
  } else {
    const i = dut1.indexOf(me);
    const around = dut1.slice(Math.max(0, i - 1), i + 2);
    rowsEl.innerHTML = `
      <div class="classement-me-card">
        <div><strong>#${me.rank}</strong><span>Ton rang</span></div>
        <div><strong>${me.signatures}</strong><span>Signatures reçues</span></div>
        <div><strong>${percent(me.signatures)}%</strong><span>Progression</span></div>
      </div>${around.map(rowHTML).join("")}`;
  }
  pagerEl.innerHTML = "";
  totalEl.textContent = `Total : ${dut1.length} DUT1 inscrits`;
}

function render() {
  if (state.tab === "general") renderGeneral();
  else renderResults();
}

function renderStats() {
  const s = donnees.statistiques;
  $("#classement-st-inscrits").textContent = fmt(s.inscrits);
  $("#classement-st-total").textContent = fmt(s.signatures);
  $("#classement-st-meilleur").textContent = fmt(s.meilleur);
  $("#classement-st-complets").textContent = fmt(s.objectif_atteint);
}

/* ===== Actualisation automatique (toutes les 30 s) ===== */
let dernierEtat = JSON.stringify(donnees.classement);

async function actualiser() {
  if (document.hidden) return; // onglet en arrière-plan : on attend le prochain tour
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
    renderStats();
    const etat = JSON.stringify(nouvelles.classement);
    if (etat === dernierEtat) return; // rien n'a changé : on ne redessine pas le tableau
    dernierEtat = etat;
    dut1 = versListe(nouvelles.classement);
    render();
  } catch {
    etatEl.textContent = "Actualisation impossible pour le moment. Nouvel essai dans 30 secondes.";
  }
}

/* ===== Événements ===== */
document.querySelectorAll(".classement-tab").forEach((btn) => {
  btn.addEventListener("click", () => {
    document.querySelectorAll(".classement-tab").forEach((b) => {
      b.classList.toggle("classement-tab-active", b === btn);
      b.setAttribute("aria-selected", b === btn);
    });
    state.tab = btn.dataset.tab;
    state.page = 1;
    render();
  });
});

$("#classement-recherche").addEventListener("input", (e) => {
  state.query = e.target.value;
  state.page = 1;
  if (state.tab === "results") {
    document.querySelector('.classement-tab[data-tab="general"]').click();
  } else {
    render();
  }
});

pagerEl.addEventListener("click", (e) => {
  const btn = e.target.closest("button[data-p]");
  if (!btn || btn.disabled) return;
  state.page = Number(btn.dataset.p);
  render();
});

/* ===== Démarrage ===== */
renderStats();
render();
const minuteur = setInterval(actualiser, ACTUALISATION_MS);
