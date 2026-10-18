/* =====================================================
   SITE SIGNATURE 2027 - Validation du formulaire d'inscription
   Utilisé par : inscription-dut1.html et inscription-parrain.html

   Rôle : vérifier les champs AVANT l'envoi. Le backend (PHP) devra
   refaire ces vérifications, car le navigateur peut être contourné.
   ===================================================== */

// true  = mode démonstration : le formulaire n'est pas envoyé, un message de succès s'affiche
// false = le formulaire est envoyé au fichier PHP indiqué dans l'attribut "action"
const MODE_DEMO = true;

const TAILLE_MAX_PHOTO = 2 * 1024 * 1024;          // 2 Mo
const TYPES_PHOTO = ["image/jpeg", "image/png"];  // JPG et PNG uniquement
const LONGUEUR_MIN_MDP = 8;

const formulaire = document.getElementById("form-inscription");
const champs = formulaire.querySelectorAll("[data-regle]");
const alerte = document.getElementById("alerte");

/* ---------- 1. Une fonction qui renvoie le message d'erreur d'un champ ---------- */
/* Elle renvoie "" (texte vide) quand le champ est correct. */
function messageErreur(champ) {
  const regle = champ.dataset.regle;

  // La photo se vérifie à part : ce n'est pas un texte mais un fichier
  if (regle === "photo") {
    const fichier = champ.files[0];
    if (!fichier) {
      return champ.required ? "Choisis une photo de profil." : "";
    }
    if (!TYPES_PHOTO.includes(fichier.type)) {
      return "La photo doit être au format JPG ou PNG.";
    }
    if (fichier.size > TAILLE_MAX_PHOTO) {
      return "La photo ne doit pas dépasser 2 Mo.";
    }
    return "";
  }

  // Le mot de passe garde ses espaces ; les autres champs sont "nettoyés" avec trim()
  const valeur = (regle === "mdp" || regle === "confirmation")
    ? champ.value
    : champ.value.trim();

  if (valeur === "") {
    return champ.required ? "Ce champ est obligatoire." : "";
  }

  const max = Number(champ.dataset.max);
  if (max && valeur.length > max) {
    return "Maximum " + max + " caractères.";
  }

  switch (regle) {
    case "utilisateur":
      if (valeur.length < 3) return "Minimum 3 caractères.";
      if (!/^[A-Za-z0-9._-]+$/.test(valeur)) {
        return "Utilise seulement des lettres, chiffres, point, tiret ou underscore.";
      }
      return "";

    case "telephone": {
      // On retire les espaces puis on teste : + suivi de 8 à 15 chiffres
      const numero = valeur.replace(/\s+/g, "");
      if (!/^\+[0-9]{8,15}$/.test(numero)) {
        return "Format international attendu, par exemple +221 77 123 45 67.";
      }
      return "";
    }

    case "mdp":
      if (valeur.length < LONGUEUR_MIN_MDP) {
        return "Minimum " + LONGUEUR_MIN_MDP + " caractères.";
      }
      return "";

    case "confirmation":
      if (valeur !== document.getElementById("mot_de_passe").value) {
        return "Les deux mots de passe ne sont pas identiques.";
      }
      return "";

    default:
      return "";
  }
}

/* ---------- 2. Afficher ou effacer l'erreur sous un champ ---------- */
function afficherErreur(champ, message) {
  const zone = document.getElementById("err-" + champ.id);
  if (zone) zone.textContent = message;

  // Pour la photo, le style rouge ne s'applique pas au champ caché
  if (champ.dataset.regle !== "photo") {
    champ.classList.toggle("invalide", message !== "");
  }
  champ.setAttribute("aria-invalid", message !== "" ? "true" : "false");
}

function verifier(champ) {
  const message = messageErreur(champ);
  afficherErreur(champ, message);
  return message === "";
}

/* ---------- 3. Vérification en direct ---------- */
champs.forEach(function (champ) {
  // Quand on quitte le champ
  champ.addEventListener("blur", function () {
    if (champ.dataset.regle !== "photo") verifier(champ);
  });

  // Pendant la frappe : on ne corrige que si une erreur est déjà affichée
  const evenement = champ.tagName === "SELECT" ? "change" : "input";
  champ.addEventListener(evenement, function () {
    if (champ.getAttribute("aria-invalid") === "true") verifier(champ);

    // Si on change le mot de passe, la confirmation doit être revérifiée
    if (champ.id === "mot_de_passe") {
      const confirmation = document.getElementById("confirmation_mot_de_passe");
      if (confirmation.value !== "") verifier(confirmation);
    }
  });
});

/* ---------- 4. Boutons "œil" pour montrer ou cacher le mot de passe ---------- */
document.querySelectorAll(".oeil").forEach(function (bouton) {
  bouton.addEventListener("click", function () {
    const champ = document.getElementById(bouton.dataset.cible);
    const visible = champ.type === "text";
    champ.type = visible ? "password" : "text";
    bouton.setAttribute("aria-pressed", String(!visible));
    bouton.setAttribute("aria-label", visible ? "Afficher le mot de passe" : "Cacher le mot de passe");
  });
});

/* ---------- 5. Aperçu de la photo choisie ---------- */
const champPhoto = document.getElementById("photo");
const apercu = document.getElementById("apercu");
const iconeDefaut = apercu.innerHTML; // on garde l'icône pour pouvoir la remettre

champPhoto.addEventListener("change", function () {
  const message = messageErreur(champPhoto);
  afficherErreur(champPhoto, message);

  const fichier = champPhoto.files[0];
  if (message === "" && fichier) {
    const lecteur = new FileReader();
    lecteur.onload = function (e) {
      apercu.innerHTML = "";
      const image = document.createElement("img");
      image.src = e.target.result;
      image.alt = "Aperçu de ta photo";
      apercu.appendChild(image);
    };
    lecteur.readAsDataURL(fichier);
  } else {
    apercu.innerHTML = iconeDefaut;
  }
});

/* ---------- 6. Envoi du formulaire ---------- */
formulaire.addEventListener("submit", function (evenement) {
  alerte.className = "alerte";
  let premierInvalide = null;

  champs.forEach(function (champ) {
    if (!verifier(champ) && premierInvalide === null) {
      premierInvalide = champ;
    }
  });

  // Au moins une erreur : on bloque l'envoi et on place le curseur sur le premier champ à corriger
  if (premierInvalide !== null) {
    evenement.preventDefault();
    premierInvalide.focus();
    alerte.className = "alerte echec visible";
    alerte.textContent = "Certains champs sont à corriger avant de continuer.";
    return;
  }

  // Tout est correct
  if (MODE_DEMO) {
    evenement.preventDefault();
    alerte.className = "alerte succes visible";
    alerte.textContent = "compte créé avec succés…";

    // En mode démonstration, aucune donnée n'est enregistrée.
    // Une fois les champs validés, retour à la page d'accueil demandée.
   window.setTimeout(function () {
  var role = new URLSearchParams(window.location.search).get("role");
  var accueil = {
    dut1: "accueil-dut1.html",
    parrain: "accueil-parrain.html"
  };
  window.location.href = accueil[role] || "accueil-dut1.html";
}, 900);
  }
  // Pour un vrai enregistrement, passer MODE_DEMO à false et configurer le traitement PHP.
});

/* ---------- 7. Messages renvoyés par le serveur (PHP) ---------- */
/* Après le traitement, PHP renvoie vers cette page avec ?succes=1 ou ?erreur=code */
const MESSAGES_SERVEUR = {
  champ_invalide: "Certaines informations sont invalides. Vérifie le formulaire et réessaie.",
  mdp_different: "Les deux mots de passe ne sont pas identiques.",
  utilisateur_pris: "Ce nom d'utilisateur est déjà utilisé. Choisis-en un autre.",
  photo_manquante: "Choisis une photo de profil.",
  photo_invalide: "La photo doit être au format JPG ou PNG, 2 Mo maximum.",
  erreur_serveur: "Une erreur est survenue sur le serveur. Réessaie dans un instant."
};

const parametres = new URLSearchParams(window.location.search);

if (parametres.get("succes") === "1") {
  alerte.className = "alerte succes visible";
  alerte.textContent = "Inscription confirmée. Redirection vers l'accueil…";

  // Seules ces deux pages sont autorisées comme destination
  const accueil = {
    dut1: "accueil-dut1.html",
    parrain: "accueil-parrain.html"
  };
  const role = parametres.get("role");
  const cible = Object.prototype.hasOwnProperty.call(accueil, role)
    ? accueil[role]
    : "accueil-dut1.html";

  window.setTimeout(function () {
    window.location.href = cible;
  }, 900);
} else if (parametres.has("erreur")) {
}



/* ---------- 8. Entrée visuelle accessible (sans modifier les fonctions existantes) ---------- */
(function animerEntreePage() {
  if (window.matchMedia && window.matchMedia("(prefers-reduced-motion: reduce)").matches) return;
  document.documentElement.classList.add("animations-actives");
})();
