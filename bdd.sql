DROP VIEW IF EXISTS vw_emprunts_materiels, vw_materiels, vw_groupes;
DROP TABLE IF EXISTS utilisateurs, groupes, materiels, emprunts;

CREATE TABLE IF NOT EXISTS utilisateurs (
    id SERIAL PRIMARY KEY,
    username VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    admin BOOLEAN DEFAULT FALSE
);

CREATE TABLE IF NOT EXISTS groupes (
    id_groupe SERIAL PRIMARY KEY,
    nom_groupe VARCHAR(255) NOT NULL,
    date_restitution DATE NOT NULL,
    est_affiche BOOLEAN NOT NULL DEFAULT TRUE
);

CREATE TABLE IF NOT EXISTS materiels (
    id_materiel SERIAL PRIMARY KEY,
    nom VARCHAR(255) NOT NULL,
    modele VARCHAR(255) NULL,
    annee INT NULL,
    etiquette_ulco VARCHAR(255) UNIQUE NULL,
    etat ENUM('OK', 'En réparation', 'Endommagé', 'Disparu') NOT NULL DEFAULT 'OK',
    -- TODO à voir si la localisation doit être NULL ou pas
    localisation VARCHAR(255) NULL, 
    descriptif TEXT NULL,
    remarque TEXT NULL
);

CREATE TABLE IF NOT EXISTS emprunts (
    id_emprunt SERIAL PRIMARY KEY,
    nom_emprunteur VARCHAR(255) NOT NULL,
    prenom_emprunteur VARCHAR(255) NOT NULL,
    id_groupe INT NOT NULL,
    id_materiel INT NOT NULL,
    date_emprunt DATE NOT NULL,
    date_prevue_restitution DATE NOT NULL,
    date_reelle_restitution DATE NULL,
    caution ENUM('Déposée', 'En attente', 'Non demandée') NOT NULL DEFAULT 'En attente',
    remarque TEXT NULL,
    FOREIGN KEY (id_groupe) REFERENCES groupes(id_groupe) ON DELETE CASCADE,
    FOREIGN KEY (id_materiel) REFERENCES materiels(id_materiel) ON DELETE CASCADE
);

INSERT INTO utilisateurs (username, password, admin) VALUES ('admininfo', md5('admin'), true);
INSERT INTO utilisateurs (username, password, admin) VALUES ('adminjrcandev', md5('admin'), true);

-- Vue pour afficher les emprunts de matériel avec les détails associés
CREATE VIEW vw_emprunts_materiels AS
SELECT 
  E.id_emprunt,
  E.nom,
  E.prenom,
  E.annee,
  TO_CHAR(E.date_emprunt, 'YYYY-MM-DD') AS date_emprunt,
  E.caution,
  M.id_materiel,
  M.nom AS nom_materiel,
  TO_CHAR(E.date_prevue_restitution, 'YYYY-MM-DD') AS date_prevue_restitution,
  TO_CHAR(E.date_reelle_restitution, 'YYYY-MM-DD') AS date_reelle_restitution,
  M.etat,
  E.remarque
FROM emprunts AS E
INNER JOIN materiels AS M ON M.id_materiel = E.id_materiel;

-- Vue pour afficher les matériels
CREATE VIEW vw_materiels AS
SELECT 
  id,
  nom,
  modele,
  TO_CHAR(date_achat, 'YYYY-MM-DD') AS annee,
  etiquette_ulco,
  localisation,
  etat,
  remarque
FROM materiels;

-- Vue pour afficher les groupes
CREATE VIEW vw_groupes AS
SELECT
    id_groupe,
    nom_groupe,
    TO_CHAR(date_restitution, 'YYYY-MM-DD') AS date_restitution,
    est_affiche
FROM groupes;

-- Vue pour afficher les utilisateurs
CREATE VIEW vw_utilisateurs AS
SELECT
    id,
    username,
    admin
FROM utilisateurs;

-- Fonction pour insérer un nouveau matériel
CREATE OR REPLACE FUNCTION create_materiel(
    nom VARCHAR,
    modele VARCHAR,
    annee INT,
    etiquette_ulco VARCHAR,
    localisation VARCHAR,
    etat ENUM('OK', 'En réparation', 'Endommagé', 'Disparu'),
    descriptif TEXT,
    remarque TEXT
) RETURNS INTEGER AS $$
DECLARE
    new_id INTEGER;
BEGIN
    INSERT INTO materiels (nom, modele, annee, etiquette_ulco, localisation, etat, descriptif, remarque)
    VALUES (nom, modele, annee, etiquette_ulco, localisation, etat, descriptif, remarque)
    RETURNING id_materiel INTO new_id;
    RETURN new_id;
END;
$$ LANGUAGE plpgsql;

-- Fonction pour insérer un nouvel emprunt
CREATE OR REPLACE FUNCTION create_emprunt(
    nom_emprunteur VARCHAR,
    prenom_emprunteur VARCHAR,
    id_groupe INT,
    id_materiel INT,
    date_emprunt DATE,
    date_prevue_restitution DATE,
    caution ENUM('Déposée', 'En attente', 'Non demandée'),
    remarque TEXT
) RETURNS INTEGER AS $$
DECLARE
    new_id INTEGER;
BEGIN
    INSERT INTO emprunts (nom_emprunteur, prenom_emprunteur, id_groupe, id_materiel, date_emprunt, date_prevue_restitution, caution, remarque)
    VALUES (nom_emprunteur, prenom_emprunteur, id_groupe, id_materiel, date_emprunt, date_prevue_restitution, caution, remarque)
    RETURNING id_emprunt INTO new_id;
    RETURN new_id;
END;
$$ LANGUAGE plpgsql;

-- Fonction pour insérer un nouveau groupe
CREATE OR REPLACE FUNCTION create_groupe(
    nom_groupe VARCHAR,
    date_restitution DATE,
    est_affiche BOOLEAN
) RETURNS INTEGER AS $$
DECLARE
    new_id INTEGER;
BEGIN
    INSERT INTO groupes (nom_groupe, date_restitution, est_affiche)
    VALUES (nom_groupe, date_restitution, est_affiche)
    RETURNING id_groupe INTO new_id;
    RETURN new_id;
END;
$$ LANGUAGE plpgsql;

-- Fonction pour créer un nouvel utilisateur
CREATE OR REPLACE FUNCTION create_utilisateur(
    username VARCHAR,
    password VARCHAR,
    admin BOOLEAN
) RETURNS INTEGER AS $$
DECLARE
    new_id INTEGER;
BEGIN
    INSERT INTO utilisateurs (username, password, admin)
    VALUES (username, md5(password), admin)
    RETURNING id INTO new_id;
    RETURN new_id;
END;
$$ LANGUAGE plpgsql;