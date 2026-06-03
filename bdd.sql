DROP VIEW IF EXISTS vw_emprunt_materiels, vw_emprunts_materiels, vw_materiels, vw_groupes, vw_stats_ordinateurs_par_annee;
DROP TABLE IF EXISTS emprunt_materiels, utilisateurs, groupes, materiels, emprunts;
DROP TYPE IF EXISTS etat_materiel, emprunt_caution;

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

CREATE TYPE etat_materiel AS ENUM ('OK', 'Réservé', 'En réparation', 'Endommagé', 'Disparu');

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
    date_emprunt DATE NOT NULL,
    date_prevue_restitution DATE NOT NULL,
    date_reelle_restitution DATE NULL,
    caution emprunt_caution NOT NULL DEFAULT 'En attente',
    remarque TEXT NULL,
    etat_restitution etat_materiel NULL,
    remarque_restitution TEXT NULL,
    FOREIGN KEY (id_groupe) REFERENCES groupes(id_groupe)
);

CREATE TABLE IF NOT EXISTS emprunt_materiels (
    id_emprunt INT NOT NULL,
    id_materiel INT NOT NULL,
    date_reelle_restitution DATE NULL,
    etat_restitution etat_materiel NULL,
    remarque_restitution TEXT NULL,
    PRIMARY KEY (id_emprunt, id_materiel),
    FOREIGN KEY (id_emprunt) REFERENCES emprunts(id_emprunt) ON DELETE CASCADE,
    FOREIGN KEY (id_materiel) REFERENCES materiels(id_materiel)
);

CREATE TABLE lots(
    id_lot SERIAL PRIMARY KEY,
    nom_lot VARCHAR(255) NOT NULL
);

CREATE TABLE lot_materiels(
    id_lot INT REFERENCES lots(id_lot) ON DELETE CASCADE,
    id_materiel INT REFERENCES materiels(id_materiel) ON DELETE CASCADE,
    PRIMARY KEY (id_lot, id_materiel)
);

INSERT INTO utilisateurs (username, password, admin) VALUES ('admin', md5('admin'), true);

-- Vue pour afficher les emprunts avec un résumé de leurs matériels
CREATE VIEW vw_emprunts_materiels AS
SELECT 
    E.id_emprunt,
    E.nom_emprunteur,
    E.prenom_emprunteur,
    G.id_groupe,
    G.nom_groupe,
    TO_CHAR(E.date_emprunt, 'YYYY-MM-DD') AS date_emprunt,
    E.caution,
    TO_CHAR(E.date_prevue_restitution, 'YYYY-MM-DD') AS date_prevue_restitution,
    TO_CHAR(E.date_reelle_restitution, 'YYYY-MM-DD') AS date_reelle_restitution,
    E.remarque,
    E.etat_restitution AS etat_restitution,
    E.remarque_restitution AS remarque_restitution,
    COUNT(EM.id_materiel) AS nombre_materiels,
    COUNT(EM.id_materiel) FILTER (WHERE EM.date_reelle_restitution IS NOT NULL) AS nombre_materiels_rendus,
    STRING_AGG(M.nom, ', ' ORDER BY M.id_materiel) AS materiels_resume,
    STRING_AGG(
        M.nom || ' ' ||
        CASE
            WHEN EM.date_reelle_restitution IS NOT NULL THEN '[rendu]'
            ELSE '[en cours]'
        END,
        ' | ' ORDER BY M.id_materiel
    ) AS materiels_details
FROM emprunts AS E
INNER JOIN groupes AS G ON G.id_groupe = E.id_groupe
LEFT JOIN emprunt_materiels AS EM ON EM.id_emprunt = E.id_emprunt
LEFT JOIN materiels AS M ON M.id_materiel = EM.id_materiel
GROUP BY
    E.id_emprunt,
    E.nom_emprunteur,
    E.prenom_emprunteur,
    G.id_groupe,
    G.nom_groupe,
    E.date_emprunt,
    E.caution,
    E.date_prevue_restitution,
    E.date_reelle_restitution,
    E.remarque,
    E.etat_restitution,
    E.remarque_restitution;

-- Vue détaillée pour afficher les matériels d'un emprunt
CREATE VIEW vw_emprunt_materiels AS
SELECT
    EM.id_emprunt,
    EM.id_materiel,
    M.nom AS nom_materiel,
    M.modele AS modele_materiel,
    M.etiquette_ulco AS etiquette_ulco_materiel,
    M.etat AS etat_materiel,
    TO_CHAR(EM.date_reelle_restitution, 'YYYY-MM-DD') AS date_reelle_restitution,
    EM.etat_restitution,
    EM.remarque_restitution
FROM emprunt_materiels AS EM
INNER JOIN materiels AS M ON M.id_materiel = EM.id_materiel;

-- Fonction pour vérifier la disponibilité d'un matériel
CREATE OR REPLACE FUNCTION is_materiel_disponible(p_id_materiel INT)
RETURNS BOOLEAN AS $$
DECLARE
    emprunt_count INT;
BEGIN
    SELECT COUNT(*) INTO emprunt_count
    FROM emprunt_materiels EM
    INNER JOIN emprunts E ON E.id_emprunt = EM.id_emprunt
    WHERE EM.id_materiel = p_id_materiel
      AND E.date_reelle_restitution IS NULL
      AND EM.date_reelle_restitution IS NULL;

    RETURN emprunt_count = 0
       AND NOT EXISTS (
           SELECT 1
           FROM materiels M
           WHERE M.id_materiel = p_id_materiel
             AND M.etat = 'Réservé'
       );
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
  remarque,
  (etat::text <> 'Réservé' AND is_materiel_disponible(id_materiel)) AS disponible,
  (
    SELECT TO_CHAR(MIN(E.date_prevue_restitution), 'YYYY-MM-DD')
    FROM emprunt_materiels EM
    INNER JOIN emprunts E ON E.id_emprunt = EM.id_emprunt
    WHERE EM.id_materiel = materiels.id_materiel
      AND E.date_reelle_restitution IS NULL
      AND EM.date_reelle_restitution IS NULL
  ) AS date_retour_prevue
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

-- Vue pour afficher les statistiques de prêts par année universitaire et groupe
CREATE OR REPLACE FUNCTION stats_materiels_filtre(recherche TEXT)
RETURNS TABLE(annee_universitaire TEXT, nom_groupe VARCHAR, nombre_prets BIGINT) AS $$
BEGIN
    RETURN QUERY
    SELECT 
        CASE 
            WHEN EXTRACT(MONTH FROM E.date_emprunt) >= 9 
            THEN EXTRACT(YEAR FROM E.date_emprunt)::text || '-' || (EXTRACT(YEAR FROM E.date_emprunt) + 1)::text
            ELSE (EXTRACT(YEAR FROM E.date_emprunt) - 1)::text || '-' || EXTRACT(YEAR FROM E.date_emprunt)::text
        END AS annee_universitaire,
        G.nom_groupe,
        COUNT(DISTINCT E.id_emprunt) AS nombre_prets
    FROM emprunts AS E
    INNER JOIN groupes AS G ON E.id_groupe = G.id_groupe
    -- On passe par la table pivot ici :
    INNER JOIN emprunt_materiels AS EM ON EM.id_emprunt = E.id_emprunt
    INNER JOIN materiels AS M ON EM.id_materiel = M.id_materiel
    WHERE M.nom ILIKE '%' || recherche || '%' OR M.modele ILIKE '%' || recherche || '%'
    GROUP BY annee_universitaire, G.nom_groupe
    ORDER BY annee_universitaire DESC, nombre_prets DESC;
END;
$$ LANGUAGE plpgsql;

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
    ids_materiels INT[],
    date_emprunt DATE,
    date_prevue_restitution DATE,
    caution emprunt_caution,
    remarque TEXT
) RETURNS INTEGER AS $$
DECLARE
    new_id INTEGER;
    id_materiel_courant INT;
BEGIN
    INSERT INTO emprunts (nom_emprunteur, prenom_emprunteur, id_groupe, date_emprunt, date_prevue_restitution, caution, remarque)
    VALUES (nom_emprunteur, prenom_emprunteur, id_groupe, date_emprunt, date_prevue_restitution, caution, remarque)
    RETURNING id_emprunt INTO new_id;

    FOREACH id_materiel_courant IN ARRAY ids_materiels LOOP
        INSERT INTO emprunt_materiels (id_emprunt, id_materiel)
        VALUES (new_id, id_materiel_courant);
    END LOOP;

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
    p_caution emprunt_caution,
    p_remarque TEXT
) RETURNS VOID AS $$
BEGIN
    UPDATE emprunts
    SET date_prevue_restitution = p_date_prevue_restitution,
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

CREATE OR REPLACE FUNCTION update_emprunt_restitution() RETURNS TRIGGER AS $$
DECLARE
    materiels_non_rendus INT;
    etat_global etat_materiel;
    remarque_globale TEXT;
BEGIN
    IF NEW.date_reelle_restitution IS NOT NULL AND OLD.date_reelle_restitution IS NULL THEN
        SELECT COUNT(*) INTO materiels_non_rendus
        FROM emprunt_materiels
        WHERE id_emprunt = NEW.id_emprunt
          AND date_reelle_restitution IS NULL;

        IF materiels_non_rendus = 0 THEN
            SELECT CASE
                WHEN BOOL_OR(etat_restitution = 'Disparu') THEN 'Disparu'::etat_materiel
                WHEN BOOL_OR(etat_restitution = 'Endommagé') THEN 'Endommagé'::etat_materiel
                WHEN BOOL_OR(etat_restitution = 'En réparation') THEN 'En réparation'::etat_materiel
                ELSE 'OK'::etat_materiel
            END INTO etat_global
            FROM emprunt_materiels
            WHERE id_emprunt = NEW.id_emprunt;

            SELECT STRING_AGG(
                CONCAT(
                    'Matériel #', id_materiel,
                    CASE WHEN remarque_restitution IS NOT NULL AND remarque_restitution <> ''
                        THEN ' : ' || remarque_restitution
                        ELSE ''
                    END
                ),
                ' | ' ORDER BY id_materiel
            ) INTO remarque_globale
            FROM emprunt_materiels
            WHERE id_emprunt = NEW.id_emprunt;

            UPDATE emprunts
            SET date_reelle_restitution = COALESCE(NEW.date_reelle_restitution, CURRENT_DATE),
                etat_restitution = etat_global,
                remarque_restitution = remarque_globale
            WHERE id_emprunt = NEW.id_emprunt;

                        UPDATE materiels
                        SET etat = NEW.etat_restitution
                        WHERE id_materiel = NEW.id_materiel
                            AND NEW.etat_restitution IS NOT NULL;
        END IF;
    END IF;

    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS trg_update_emprunt_restitution ON emprunt_materiels;
CREATE TRIGGER trg_update_emprunt_restitution
AFTER UPDATE ON emprunt_materiels
FOR EACH ROW
WHEN (NEW.date_reelle_restitution IS NOT NULL AND OLD.date_reelle_restitution IS NULL)
EXECUTE FUNCTION update_emprunt_restitution();

CREATE OR REPLACE FUNCTION rendre_materiel(
    p_id_emprunt INT,
    p_id_materiel INT,
    p_etat_restitution etat_materiel,
    p_remarque_restitution TEXT,
    p_date_reelle_restitution DATE
) RETURNS BOOLEAN AS $$
DECLARE
    v_updated_count INT;
BEGIN
    UPDATE emprunt_materiels
    SET date_reelle_restitution = COALESCE(p_date_reelle_restitution, CURRENT_DATE),
        etat_restitution = p_etat_restitution,
        remarque_restitution = p_remarque_restitution
    WHERE id_emprunt = p_id_emprunt
      AND id_materiel = p_id_materiel
      AND date_reelle_restitution IS NULL;

    -- récupère le nombre de lignes affectées
    GET DIAGNOSTICS v_updated_count = ROW_COUNT;

    IF v_updated_count > 0 AND p_etat_restitution IS NOT NULL THEN
        UPDATE materiels
        SET etat = p_etat_restitution
        WHERE id_materiel = p_id_materiel;
    END IF;

    RETURN v_updated_count > 0;
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION get_emprunts_by_materiel(p_id_materiel INT)
RETURNS TABLE(
    id_emprunt INT,
    nom_emprunteur VARCHAR,
    prenom_emprunteur VARCHAR,
    nom_groupe VARCHAR,
    date_emprunt TEXT,
    date_prevue_restitution TEXT,
    date_reelle_restitution TEXT,
    remarque_restitution TEXT,
    etat_restitution etat_materiel
) AS $$
BEGIN
    RETURN QUERY
    SELECT
        E.id_emprunt,
        E.nom_emprunteur,
        E.prenom_emprunteur,
        G.nom_groupe,
        TO_CHAR(E.date_emprunt, 'YYYY-MM-DD'),
        TO_CHAR(E.date_prevue_restitution, 'YYYY-MM-DD'),
        TO_CHAR(EM.date_reelle_restitution, 'YYYY-MM-DD'),
        EM.remarque_restitution,
        EM.etat_restitution
    FROM emprunt_materiels EM
    INNER JOIN emprunts E ON E.id_emprunt = EM.id_emprunt
    INNER JOIN groupes G ON G.id_groupe = E.id_groupe
    WHERE EM.id_materiel = p_id_materiel
    ORDER BY E.id_emprunt DESC;
END;
$$ LANGUAGE plpgsql;