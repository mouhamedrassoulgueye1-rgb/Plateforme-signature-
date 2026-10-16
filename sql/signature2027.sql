-- =============================================================================================
-- Base de données Site Signature 2027 (MySQL 8 / MariaDB de XAMPP)
-- À exécuter dans phpMyAdmin (onglet SQL) ou : mysql -u root < sql/signature2027.sql
-- Toute modification de ce fichier passe par une pull request validée par le groupe (fichier Commun).
-- Les requêtes de lecture sont dans includes/signatures.php, pas ici.
--
-- Conventions : tables au singulier en minuscules, colonnes en snake_case,
-- clé primaire id_<table>, téléphone stocké en chiffres avec l'indicatif (221771234567).
-- =============================================================================================

CREATE DATABASE IF NOT EXISTS signature2027 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE signature2027;

CREATE TABLE IF NOT EXISTS parrain (
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

CREATE TABLE IF NOT EXISTS filleul (
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
CREATE TABLE IF NOT EXISTS signature (
  id_parrain     INT NOT NULL,
  id_filleul     INT NOT NULL,
  date_signature DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_parrain, id_filleul),
  CONSTRAINT fk_sig_parrain FOREIGN KEY (id_parrain)
    REFERENCES parrain(id_parrain) ON DELETE CASCADE,
  CONSTRAINT fk_sig_filleul FOREIGN KEY (id_filleul)
    REFERENCES filleul(id_filleul) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS administrateur (
  id_admin        INT AUTO_INCREMENT PRIMARY KEY,
  nom             VARCHAR(50)  NOT NULL,
  prenom          VARCHAR(80)  NOT NULL,
  nom_utilisateur VARCHAR(30)  NOT NULL UNIQUE,
  mot_de_passe    VARCHAR(255) NOT NULL
) ENGINE=InnoDB;

-- Premier administrateur : générez le hash du mot de passe, puis remplacez <HASH> :
--   php -r "echo password_hash('votre_mot_de_passe', PASSWORD_DEFAULT);"
-- INSERT INTO administrateur (nom, prenom, nom_utilisateur, mot_de_passe)
-- VALUES ('Nom', 'Prénom', 'admin', '<HASH>');
