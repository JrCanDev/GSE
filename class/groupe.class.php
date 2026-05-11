<?php
class Groupe
{
    private int $id_groupe;
    private string $nom_groupe;
    private string $date_restitution;
    private bool $est_affiche;

    private PDO $db;

    public function __construct(PDO $db, array $data = [])
    {
        $this->db = $db;

        $this->id_groupe = -1;
        $this->nom_groupe = '';
        $this->date_restitution = '';
        $this->est_affiche = true;

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
        return $this->nom_groupe . ' (ID: ' . $this->id_groupe . ')' . ' - Restitution: ' . $this->date_restitution . ' - Affiché: ' . ($this->est_affiche ? 'Oui' : 'Non');
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
            $fields = array('nom_groupe', 'date_restitution');
            $sql = 'SELECT create_groupe(:' . implode(', :', $fields) . ') AS id_groupe';
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':nom_groupe', $this->nom_groupe, PDO::PARAM_STR);
            $stmt->bindValue(':date_restitution', $this->date_restitution, PDO::PARAM_STR);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($result) {
                $this->id_groupe = $result['id_groupe'];
            }
        } catch (PDOException $e) {
            $_SESSION['mesgs']['errors'][] = "ERREUR Base de données : " . $e->getMessage();
        }
    }

    public function fetch(int $identifier): void
    {
        try {
            $fields = array('id_groupe', 'nom_groupe', 'date_restitution', 'est_affiche');

            $sql = 'SELECT ' . implode(', ', $fields) . ' FROM vw_groupes WHERE id_groupe = :id_groupe';
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id_groupe', $identifier, PDO::PARAM_INT);
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
            $fields = array('id_groupe', 'nom_groupe', 'date_restitution', 'est_affiche');
            $sql = 'SELECT update_groupe(:' . implode(', :', $fields) . ')';
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id_groupe', $this->id_groupe, PDO::PARAM_INT);
            $stmt->bindValue(':nom_groupe', $this->nom_groupe, PDO::PARAM_STR);
            $stmt->bindValue(':date_restitution', $this->date_restitution, PDO::PARAM_STR);
            $stmt->bindValue(':est_affiche', $this->est_affiche, PDO::PARAM_BOOL);
            $stmt->execute();
        } catch (PDOException $e) {
            $_SESSION['mesgs']['errors'][] = "ERREUR Base de données : " . $e->getMessage();
        }
    }

    public function delete(): void
    {
        try {
            $sql = 'SELECT delete_groupe(:id_groupe)';
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id_groupe', $this->id_groupe, PDO::PARAM_INT);
            $stmt->execute();
        } catch (PDOException $e) {
            $_SESSION['mesgs']['errors'][] = "ERREUR Base de données : " . $e->getMessage();
        }
    }

    public static function fetchAll(PDO $db): array
    {
        try {
            $fields = array('id_groupe', 'nom_groupe', 'date_restitution', 'est_affiche');
            $sql = 'SELECT ' . implode(', ', $fields) . ' FROM vw_groupes';
            $stmt = $db->query($sql);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $groupes = [];
            if ($result) {
                foreach ($result as $data) {
                    $groupes[] = new Groupe($db, $data);
                }
            }

            return $groupes;
        } catch (PDOException $e) {
            $_SESSION['mesgs']['errors'][] = "ERREUR Base de données : " . $e->getMessage();
            return [];
        }
    }
}
