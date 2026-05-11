<?php
class Emprunt
{
    private int $id_emprunt;
    private string $nom_emprunteur;
    private string $prenom_emprunteur;
    private int $id_groupe;
    private string $nom_groupe;
    private int $id_materiel;
    private string $nom_materiel;
    private string $modele_materiel;
    private string $etiquette_ulco_materiel;
    private string $date_emprunt;
    private string $date_prevue_restitution;
    private ?string $date_reelle_restitution;
    private string $caution;
    private string $etat;
    private string $remarque;

    public static array $cautions = array('Déposée', 'En attente', 'Non demandée');

    private PDO $db;

    public function __construct(PDO $db, array $data = [])
    {
        $this->db = $db;

        $this->id_emprunt = -1;
        $this->nom_emprunteur = '';
        $this->prenom_emprunteur = '';
        $this->id_groupe = -1;
        $this->nom_groupe = '';
        $this->id_materiel = -1;
        $this->nom_materiel = '';
        $this->modele_materiel = '';
        $this->etiquette_ulco_materiel = '';
        $this->date_emprunt = '';
        $this->date_prevue_restitution = '';
        $this->date_reelle_restitution = '';
        $this->caution = '';
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
        // return $this->nom_emprunteur . ' ' . $this->prenom_emprunteur . ' (ID: ' . $this->id_emprunt . ')';
        return $this->nom_emprunteur . ' ' . $this->prenom_emprunteur . ' (ID: ' . $this->id_emprunt . ')'  . ' - ' . $this->nom_materiel . ' (ID Matériel: ' . $this->id_materiel . ')' . ' - ' . 'Modèle: ' . $this->modele_materiel . ' - ' . 'Étiquette ULCO: ' . $this->etiquette_ulco_materiel;
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
            $fields = array('nom_emprunteur', 'prenom_emprunteur', 'id_groupe', 'id_materiel', 'date_emprunt', 'date_prevue_restitution', 'caution', 'remarque');
            $sql = 'SELECT create_emprunt(:' . implode(', :', $fields) . ') AS id_emprunt';
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':nom_emprunteur', $this->nom_emprunteur, PDO::PARAM_STR);
            $stmt->bindValue(':prenom_emprunteur', $this->prenom_emprunteur, PDO::PARAM_STR);
            $stmt->bindValue(':id_groupe', $this->id_groupe, PDO::PARAM_INT);
            $stmt->bindValue(':id_materiel', $this->id_materiel, PDO::PARAM_INT);
            $stmt->bindValue(':date_emprunt', $this->date_emprunt, PDO::PARAM_STR);
            $stmt->bindValue(':date_prevue_restitution', $this->date_prevue_restitution, PDO::PARAM_STR);
            $stmt->bindValue(':caution', $this->caution, PDO::PARAM_STR);
            $stmt->bindValue(':remarque', $this->remarque, PDO::PARAM_STR);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($result) {
                $this->id_emprunt = $result['id_emprunt'];
            }
        } catch (PDOException $e) {
            $_SESSION['mesgs']['errors'][] = "ERREUR Base de données : " . $e->getMessage();
        }
    }

    public function fetch(int $identifier): void
    {
        try {
            $fields = array('id_emprunt', 'nom_emprunteur', 'prenom_emprunteur', 'id_groupe', 'nom_groupe', 'date_emprunt', 'caution', 'id_materiel', 'nom_materiel', 'date_prevue_restitution', 'date_reelle_restitution', 'etat', 'remarque');

            $sql = 'SELECT ' . implode(', ', $fields) . ' FROM vw_emprunts_materiels WHERE id_emprunt = :id_emprunt';
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id_emprunt', $identifier, PDO::PARAM_INT);
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
            $fields = array('id_emprunt', 'date_prevue_restitution', 'date_reelle_restitution', 'caution', 'remarque');
            $sql = 'SELECT update_emprunt(:' . implode(', :', $fields) . ')';
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id_emprunt', $this->id_emprunt, PDO::PARAM_INT);
            $stmt->bindValue(':date_prevue_restitution', $this->date_prevue_restitution, PDO::PARAM_STR);
            $stmt->bindValue(':date_reelle_restitution', $this->date_reelle_restitution, PDO::PARAM_STR);
            $stmt->bindValue(':caution', $this->caution, PDO::PARAM_STR);
            $stmt->bindValue(':remarque', $this->remarque, PDO::PARAM_STR);
            $stmt->execute();
        } catch (PDOException $e) {
            $_SESSION['mesgs']['errors'][] = "ERREUR Base de données : " . $e->getMessage();
        }
    }

    public static function fetchAll(PDO $db): array
    {
        try {
            $fields = array('id_emprunt', 'nom_emprunteur', 'prenom_emprunteur', 'nom_groupe', 'id_materiel', 'nom_materiel', 'modele_materiel', 'etiquette_ulco_materiel', 'date_emprunt', 'date_prevue_restitution', 'date_reelle_restitution', 'caution', 'etat', 'remarque');
            $sql = 'SELECT ' . implode(', ', $fields) . ' FROM vw_emprunts_materiels';
            $stmt = $db->query($sql);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $emprunts = [];
            if ($result) {
                foreach ($result as $data) {
                    $emprunts[] = new Emprunt($db, $data);
                }
            }

            return $emprunts;
        } catch (PDOException $e) {
            $_SESSION['mesgs']['errors'][] = "ERREUR Base de données : " . $e->getMessage();
            return [];
        }
    }
}
