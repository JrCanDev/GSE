<?php
class Entite {
    private int $id;
    private string $nom;

    private PDO $db;

    public function __construct(PDO $db, array $data = [])
    {
        $this->db = $db;

        $this->id = -1;
        $this->nom = '';

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

    public function __isset($name): bool
    {
        return property_exists($this, $name) && isset($this->$name);
    }

    public function __toString(): string
    {
        return $this->nom . ' (ID: ' . $this->id . ')';
    }

    public function hydrate(array $data): void
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key))
                $this->$key = $value;
        }
    }

    public function fetch(int $identifier): void
    {
        try {
            $fields = array('id', 'nom');

            $sql = 'SELECT ' . implode(', ', $fields) . ' FROM entites WHERE id = :id';
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $identifier, PDO::PARAM_INT);
            $stmt->execute();
            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($data) $this->hydrate($data);
        } catch (PDOException $e) {
            $_SESSION['mesgs']['errors'][] = "ERREUR lors de la récupération de l'entité : " . $e->getMessage();
        }
    }

    public static function fetchAll(PDO $db): array
    {
        try {
            $fields = array('id', 'nom');
            $sql = 'SELECT ' . implode(', ', $fields) . ' FROM entites';
            $stmt = $db->query($sql);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $entites = [];
            if ($result) {
                foreach ($result as $data) {
                    $entites[] = new Entite($db, $data);
                }
            }

            return $entites;
        } catch (PDOException $e) {
            $_SESSION['mesgs']['errors'][] = "ERREUR lors de la récupération des entités : " . $e->getMessage();
            return [];
        }
    }
}