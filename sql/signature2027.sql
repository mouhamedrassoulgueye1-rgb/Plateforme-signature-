CREATE DATABASE IF NOT EXISTS signature2027 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE signature2027;

CREATE TABLE parrain (
  id_parrain       INT AUTO_INCREMENT PRIMARY KEY,
  nom              VARCHAR(50)  NOT NULL,
  prenom           VARCHAR(80)  NOT NULL,
  nom_utilisateur  VARCHAR(30)  NOT NULL UNIQUE,
  telephone        VARCHAR(20)  NOT NULL,
  type_parrain     ENUM('Parrain','Marraine','Mame1','Mame2','Mame3','Alumni') NOT NULL,
  chambre          VARCHAR(30)  NOT NULL,
  photo            VARCHAR(255) NULL,
  mot_de_passe     VARCHAR(255) NOT NULL,
  date_inscription DATETIME DEFAULT CURRENT_TIMESTAMP,
  statut_compte    ENUM('actif','suspendu') DEFAULT 'actif'
) ENGINE=InnoDB;

CREATE TABLE filleul (
  id_filleul       INT AUTO_INCREMENT PRIMARY KEY,
  nom              VARCHAR(50)  NOT NULL,
  prenom           VARCHAR(80)  NOT NULL,
  nom_utilisateur  VARCHAR(30)  NOT NULL UNIQUE,
  telephone        VARCHAR(20)  NOT NULL,
  photo            VARCHAR(255) NULL,
  mot_de_passe     VARCHAR(255) NOT NULL,
  date_inscription DATETIME DEFAULT CURRENT_TIMESTAMP,
  statut_compte    ENUM('actif','suspendu') DEFAULT 'actif'
) ENGINE=InnoDB;

-- La clé primaire composée empêche un parrain de signer deux fois pour le même DUT1 (RG6)
CREATE TABLE signature (
  id_parrain     INT NOT NULL,
  id_filleul     INT NOT NULL,
  date_signature DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_parrain, id_filleul),
  CONSTRAINT fk_sig_parrain FOREIGN KEY (id_parrain)
    REFERENCES parrain(id_parrain) ON DELETE CASCADE,
  CONSTRAINT fk_sig_filleul FOREIGN KEY (id_filleul)
    REFERENCES filleul(id_filleul) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE administrateur (
  id_admin        INT AUTO_INCREMENT PRIMARY KEY,
  nom             VARCHAR(50)  NOT NULL,
  prenom          VARCHAR(80)  NOT NULL,
  nom_utilisateur VARCHAR(30)  NOT NULL UNIQUE,
  mot_de_passe    VARCHAR(255) NOT NULL
) ENGINE=InnoDB;

-- Compteur de signatures d'un DUT1 (page d'accueil DUT1)
SELECT COUNT(*) AS nb_signatures FROM signature WHERE id_filleul = ?;

-- Liste des signataires d'un DUT1 avec téléphone pour le lien WhatsApp
SELECT p.prenom, p.nom, p.type_parrain, p.telephone, s.date_signature
FROM signature s JOIN parrain p ON p.id_parrain = s.id_parrain
WHERE s.id_filleul = ? ORDER BY s.date_signature;

-- Classement des DUT1 par nombre de signatures (rang)
SELECT f.id_filleul, f.prenom, f.nom,
       COUNT(s.id_parrain) AS nb_signatures,
       RANK() OVER (ORDER BY COUNT(s.id_parrain) DESC) AS rang
FROM filleul f LEFT JOIN signature s ON s.id_filleul = f.id_filleul
GROUP BY f.id_filleul, f.prenom, f.nom;

-- Nombre de signatures données par un parrain + liste des DUT1 concernés
SELECT f.prenom, f.nom, f.telephone, s.date_signature
FROM signature s JOIN filleul f ON f.id_filleul = s.id_filleul
WHERE s.id_parrain = ?;