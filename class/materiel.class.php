<?php
class Materiel
{
    private int $id_materiel;
    private string $nom;
    private string $modele;
    private string $annee;
    private string $etiquette_ulco;
    private string $etat;
    private string $localisation;
    private string $descriptif;
    private string $remarque;

    private PDO $db;

    public function __construct(PDO $db, array $data = [])
    {
        $this->db = $db;

        $this->id_materiel = -1;
        $this->nom = '';
        $this->modele = '';
        $this->annee = '';
        $this->etiquette_ulco = '';
        $this->etat = '';
        $this->localisation = '';
        $this->descriptif = '';
        $this->remarque = '';

        if (!empty($data))
            $this->hydrate($data);
    }

    public function __get($name)
    {
        if (property_exists($this, $name))
            return $this->$name;
        return null;
    }

    public function __set($name, $value): void
    {
        if (property_exists($this, $name))
            $this->$name = $value;
    }

    public function __toString(): string
    {
        return $this->nom . ' (ID: ' . $this->id_materiel . ')' . ' - Modèle: ' . $this->modele . ' - Année: ' . $this->annee;
    }

    public function hydrate(array $data): void
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key))
                $this->$key = $value;
        }
    }

    public function create(): void
    {
        try {
            $fields = array('nom', 'modele', 'annee', 'etiquette_ulco', 'etat', 'localisation', 'descriptif', 'remarque');
            $sql = 'SELECT create_materiel(: ' . implode(', :', $fields) . ')';
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':nom', $this->nom, PDO::PARAM_STR);
            $stmt->bindValue(':modele', $this->modele, PDO::PARAM_STR);
            $stmt->bindValue(':annee', $this->annee, PDO::PARAM_STR);
            $stmt->bindValue(':etiquette_ulco', $this->etiquette_ulco, PDO::PARAM_STR);
            $stmt->bindValue(':etat', $this->etat, PDO::PARAM_STR);
            $stmt->bindValue(':localisation', $this->localisation, PDO::PARAM_STR);
            $stmt->bindValue(':descriptif', $this->descriptif, PDO::PARAM_STR);
            $stmt->bindValue(':remarque', $this->remarque, PDO::PARAM_STR);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($result) {
                $this->id_materiel = $result['id_materiel'];
            }
        } catch (PDOException $e) {
            $_SESSION['mesgs']['errors'][] = "ERREUR Base de données : " . $e->getMessage();
        }
    }

    public function fetch(int $identifier): void
    {
        try {
            $fields = array('id_materiel', 'nom', 'modele', 'annee', 'etiquette_ulco', 'etat', 'localisation', 'descriptif', 'remarque');

            $sql = 'SELECT ' . implode(', ', $fields) . ' FROM vw_materiels WHERE id_materiel = :id_materiel';
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id_materiel', $identifier, PDO::PARAM_INT);
            $stmt->execute();
            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($data)
                $this->hydrate($data);
        } catch (PDOException $e) {
            $_SESSION['mesgs']['errors'][] = "ERREUR Base de données : " . $e->getMessage();
        }
    }

    public function update(): void
    {
        try {
            $fields = array('id_materiel', 'nom', 'modele', 'annee', 'etiquette_ulco', 'etat', 'localisation', 'descriptif', 'remarque');
            $sql = 'SELECT update_materiel(: ' . implode(', :', $fields) . ')';
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id_materiel', $this->id_materiel, PDO::PARAM_INT);
            $stmt->bindValue(':nom', $this->nom, PDO::PARAM_STR);
            $stmt->bindValue(':modele', $this->modele, PDO::PARAM_STR);
            $stmt->bindValue(':annee', $this->annee, PDO::PARAM_STR);
            $stmt->bindValue(':etiquette_ulco', $this->etiquette_ulco, PDO::PARAM_STR);
            $stmt->bindValue(':etat', $this->etat, PDO::PARAM_STR);
            $stmt->bindValue(':localisation', $this->localisation, PDO::PARAM_STR);
            $stmt->bindValue(':descriptif', $this->descriptif, PDO::PARAM_STR);
            $stmt->bindValue(':remarque', $this->remarque, PDO::PARAM_STR);
            $stmt->execute();
        } catch (PDOException $e) {
            $_SESSION['mesgs']['errors'][] = "ERREUR Base de données : " . $e->getMessage();
        }
    }

    public static function fetchAll(PDO $db): array
    {
        try {
            $fields = array('id_materiel', 'nom', 'modele', 'annee', 'etiquette_ulco', 'etat', 'localisation', 'descriptif', 'remarque');
            $sql = 'SELECT ' . implode(', ', $fields) . ' FROM vw_materiels';
            $stmt = $db->query($sql);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $materiels = [];
            if ($result) {
                foreach ($result as $data) {
                    $materiels[] = new Materiel($db, $data);
                }
            }

            return $materiels;
        } catch (PDOException $e) {
            $_SESSION['mesgs']['errors'][] = "ERREUR Base de données : " . $e->getMessage();
            return [];
        }
    }
}
