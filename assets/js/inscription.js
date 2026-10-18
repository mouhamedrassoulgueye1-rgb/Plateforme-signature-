/* =====================================================
   SITE SIGNATURE 2027 - Validation du formulaire d'inscription
   Utilisé par : inscription/dut1.php et inscription/parrain.php

   Rôle : vérifier les champs AVANT l'envoi. Le serveur (inscription/fonctions_inscription.php)
   refait toutes ces vérifications, car le navigateur peut être contourné.
   ===================================================== */

const TAILLE_MAX_PHOTO = 2 * 1024 * 1024;          // 2 Mo
const TYPES_PHOTO = ["image/jpeg", "image/png", "image/webp"];  // mêmes formats que enregistrer_photo()
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
      return "La photo doit être au format JPG, PNG ou WebP.";
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
      // Même règle que nom_utilisateur_valide() : le serveur met le nom en minuscules
      if (!/^[a-z0-9._]+$/.test(valeur.toLowerCase())) {
        return "Utilise seulement des lettres, des chiffres, le point ou le tiret bas (_).";
      }
      return "";

    case "telephone": {
      // Même règle que normaliser_telephone() : 9 chiffres (Sénégal) ou numéro avec indicatif
      const numero = valeur.replace(/[\s().-]+/g, "");
      if (!/^(\+|00)?[0-9]{9,15}$/.test(numero)) {
        return "Numéro invalide, par exemple 77 123 45 67 ou +221 77 123 45 67.";
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

/* ---------- 4. Bouton « Afficher » du mot de passe : géré par main.js (commun) ---------- */

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

  // Tout est correct : le formulaire part vers le serveur
});

/* ---------- 7. Erreurs renvoyées par le serveur ---------- */
/* Le PHP écrit le message sous le champ (p.message-erreur) : on marque le champ en rouge. */
champs.forEach(function (champ) {
  const zone = document.getElementById("err-" + champ.id);
  if (zone && zone.textContent.trim() !== "") afficherErreur(champ, zone.textContent.trim());
});

/* ---------- 8. Entrée visuelle accessible (sans modifier les fonctions existantes) ---------- */
(function animerEntreePage() {
  if (window.matchMedia && window.matchMedia("(prefers-reduced-motion: reduce)").matches) return;
  document.documentElement.classList.add("animations-actives");
})();
