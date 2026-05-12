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

CREATE TYPE etat_materiel AS ENUM ('OK', 'En réparation', 'Endommagé', 'Disparu');

CREATE TABLE IF NOT EXISTS materiels (
    id_materiel SERIAL PRIMARY KEY,
    nom VARCHAR(255) NOT NULL,
    modele VARCHAR(255) NULL,
    annee INT NULL,
    etiquette_ulco VARCHAR(255) UNIQUE NULL,
    etat etat_materiel NOT NULL DEFAULT 'OK',
    localisation VARCHAR(255) NOT NULL, 
    descriptif TEXT NULL,
    remarque TEXT NULL
);

CREATE TYPE emprunt_caution AS ENUM ('Déposée', 'En attente', 'Non demandée');

CREATE TABLE IF NOT EXISTS emprunts (
    id_emprunt SERIAL PRIMARY KEY,
    nom_emprunteur VARCHAR(255) NOT NULL,
    prenom_emprunteur VARCHAR(255) NOT NULL,
    id_groupe INT NOT NULL,
    id_materiel INT NOT NULL,
    date_emprunt DATE NOT NULL,
    date_prevue_restitution DATE NOT NULL,
    date_reelle_restitution DATE NULL,
    caution emprunt_caution NOT NULL DEFAULT 'En attente',
    remarque TEXT NULL,
    etat_restitution etat_materiel NULL,
    remarque_restitution TEXT NULL,
    FOREIGN KEY (id_groupe) REFERENCES groupes(id_groupe) ON DELETE CASCADE,
    FOREIGN KEY (id_materiel) REFERENCES materiels(id_materiel) ON DELETE CASCADE
);

INSERT INTO utilisateurs (username, password, admin) VALUES ('admininfo', md5('admin'), true);
INSERT INTO utilisateurs (username, password, admin) VALUES ('adminjrcandev', md5('admin'), true);

-- Vue pour afficher les emprunts de matériel avec les détails associés
CREATE VIEW vw_emprunts_materiels AS
SELECT 
  E.id_emprunt,
  E.nom_emprunteur,
  E.prenom_emprunteur,
  G.id_groupe,
  G.nom_groupe,
  TO_CHAR(E.date_emprunt, 'YYYY-MM-DD') AS date_emprunt,
  E.caution,
  M.id_materiel AS id_materiel,
  M.nom AS nom_materiel,
  M.modele AS modele_materiel,
  M.etiquette_ulco AS etiquette_ulco_materiel,
  TO_CHAR(E.date_prevue_restitution, 'YYYY-MM-DD') AS date_prevue_restitution,
  TO_CHAR(E.date_reelle_restitution, 'YYYY-MM-DD') AS date_reelle_restitution,
  M.etat,
  E.remarque,
  E.etat_restitution AS etat_restitution,
  E.remarque_restitution AS remarque_restitution
FROM emprunts AS E
INNER JOIN materiels AS M ON M.id_materiel = E.id_materiel
INNER JOIN groupes AS G ON G.id_groupe = E.id_groupe;

-- Fonction pour vérifier la disponibilité d'un matériel
CREATE OR REPLACE FUNCTION is_materiel_disponible(p_id_materiel INT)
RETURNS BOOLEAN AS $$
DECLARE
    emprunt_count INT;
BEGIN
    SELECT COUNT(*) INTO emprunt_count
    FROM emprunts
    WHERE id_materiel = p_id_materiel
      AND date_reelle_restitution IS NULL;

    RETURN emprunt_count = 0;
END;
$$ LANGUAGE plpgsql;

-- Vue pour afficher les matériels
CREATE VIEW vw_materiels AS
SELECT 
  id_materiel,
  nom,
  modele,
  annee,
  etiquette_ulco,
  localisation,
  etat,
  descriptif,
  remarque
FROM materiels
ORDER BY id_materiel;

-- Vue pour afficher les groupes
CREATE VIEW vw_groupes AS
SELECT
    id_groupe,
    nom_groupe,
    TO_CHAR(date_restitution, 'YYYY-MM-DD') AS date_restitution,
    est_affiche
FROM groupes
ORDER BY id_groupe;

-- Vue pour afficher les utilisateurs
CREATE VIEW vw_utilisateurs AS
SELECT
    id,
    username,
    admin
FROM utilisateurs
ORDER BY id;

-- Fonction pour insérer un nouveau matériel
CREATE OR REPLACE FUNCTION create_materiel(
    nom VARCHAR,
    modele VARCHAR,
    annee INT,
    etiquette_ulco VARCHAR,
    localisation VARCHAR,
    etat etat_materiel,
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
    caution emprunt_caution,
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
    date_restitution DATE
) RETURNS INTEGER AS $$
DECLARE
    new_id INTEGER;
BEGIN
    INSERT INTO groupes (nom_groupe, date_restitution) VALUES (nom_groupe, date_restitution)
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

-- Fonction pour mettre à jour un matériel
CREATE OR REPLACE FUNCTION update_materiel(
    p_id_materiel INTEGER,
    p_nom VARCHAR,
    p_modele VARCHAR,
    p_annee INT,
    p_etiquette_ulco VARCHAR,
    p_localisation VARCHAR,
    p_etat etat_materiel,
    p_descriptif TEXT,
    p_remarque TEXT
) RETURNS VOID AS $$
BEGIN
    UPDATE materiels
    SET nom = p_nom,
        modele = p_modele,
        annee = p_annee,
        etiquette_ulco = p_etiquette_ulco,
        localisation = p_localisation,
        etat = p_etat,
        descriptif = p_descriptif,
        remarque = p_remarque
    WHERE id_materiel = p_id_materiel;
END;
$$ LANGUAGE plpgsql;

-- Fonction pour mettre à jour un emprunt
CREATE OR REPLACE FUNCTION update_emprunt(
    p_id_emprunt INTEGER,
    p_date_prevue_restitution DATE,
    p_date_reelle_restitution DATE,
    p_caution emprunt_caution,
    p_remarque TEXT
) RETURNS VOID AS $$
BEGIN
    UPDATE emprunts
    SET date_prevue_restitution = p_date_prevue_restitution,
        date_reelle_restitution = p_date_reelle_restitution,
        caution = p_caution,
        remarque = p_remarque
    WHERE id_emprunt = p_id_emprunt;
END;
$$ LANGUAGE plpgsql;

-- Fonction pour mettre à jour un groupe
CREATE OR REPLACE FUNCTION update_groupe(
    p_id_groupe INTEGER,
    p_nom_groupe VARCHAR,
    p_date_restitution DATE,
    p_est_affiche BOOLEAN
) RETURNS VOID AS $$
BEGIN
    UPDATE groupes
    SET nom_groupe = p_nom_groupe,
        date_restitution = p_date_restitution,
        est_affiche = p_est_affiche
    WHERE id_groupe = p_id_groupe;
END;
$$ LANGUAGE plpgsql;

-- Fonction pour mettre à jour un utilisateur
CREATE OR REPLACE FUNCTION update_utilisateur(
    p_id INTEGER,
    p_username VARCHAR,
    p_password VARCHAR,
    p_admin BOOLEAN
) RETURNS VOID AS $$
BEGIN
    UPDATE utilisateurs
    SET username = p_username,
        password = md5(p_password),
        admin = p_admin
    WHERE id = p_id;
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION delete_materiel(p_id_materiel INTEGER)
RETURNS VOID AS $$
BEGIN
    DELETE FROM materiels WHERE id_materiel = p_id_materiel;
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION delete_groupe(p_id_groupe INTEGER)
RETURNS VOID AS $$
BEGIN
    DELETE FROM groupes WHERE id_groupe = p_id_groupe;
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION update_emprunt_restitution() RETURNS TRIGGER AS $$
DECLARE
    v_etat materiels.etat%TYPE;
BEGIN
    IF NEW.date_reelle_restitution IS NOT NULL AND OLD.date_reelle_restitution IS NULL THEN
        SELECT etat INTO v_etat
        FROM materiels
        WHERE id_materiel = NEW.id_materiel;

        NEW.etat_restitution := v_etat;
        NEW.remarque_restitution := OLD.remarque;
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE TRIGGER trg_update_emprunt_restitution
BEFORE UPDATE ON emprunts
FOR EACH ROW
WHEN (NEW.date_reelle_restitution IS NOT NULL AND OLD.date_reelle_restitution IS NULL)
EXECUTE FUNCTION update_emprunt_restitution();