<?php
class Utilisateur
{
    private int $id;
    private string $username;
    private string $password;
    private ?int $entite_id;
    private bool $admin;

    private PDO $db;

    public function __construct(PDO $db, array $data = [])
    {
        $this->db = $db;

        $this->id = -1;
        $this->username = '';
        $this->password = '';
        $this->entite_id = -1;
        $this->admin = false;

        if (!empty($data)) {
            $this->hydrate($data);
        }
    }

    public function __get($name)
    {
        if (property_exists($this, $name)) {
            return $this->$name;
        }
        return null;
    }

    public function __set($name, $value): void
    {
        if (property_exists($this, $name)) {
            $this->$name = $value;
        }
    }

    public function __isset($name): bool
    {
        return property_exists($this, $name) && isset($this->$name);
    }

    public function __toString(): string
    {
        return $this->username . ' (ID: ' . $this->id . ')' . ($this->admin ? ' [Admin]' : '');
    }

    public function hydrate(array $data): void
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    public function create(): void
    {
        try {
            $sql = "INSERT INTO utilisateurs (username, password, admin, entite_id) VALUES (:username, :password, :admin, :entite_id)";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':username', $this->username, PDO::PARAM_STR);
            $stmt->bindValue(':password', $this->password, PDO::PARAM_STR);
            $stmt->bindValue(':admin', $this->admin, PDO::PARAM_BOOL);
            $stmt->bindValue(':entite_id', $this->entite_id, PDO::PARAM_INT);
            $stmt->execute();

            $this->id = (int) $this->db->lastInsertId();

            $_SESSION['mesgs']['confirm'][] = "Utilisateur créé avec succès.";
        } catch (Exception $e) {
            $_SESSION['mesgs']['errors'][] = "Erreur lors de la création de l'utilisateur : " . $e->getMessage();
        }
    }

    public function fetch(int $id): void
    {
        try {
            $sql = "SELECT id, username, password, admin, entite_id FROM utilisateurs WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($data) {
                $this->hydrate($data);
            }
        } catch (Exception $e) {
            $_SESSION['mesgs']['errors'][] = "Erreur lors de la récupération de l'utilisateur : " . $e->getMessage();
        }
    }

    public function delete(): void
    {
        if ($this->id === -1) {
            return;
        }

        try {
            $sql = "DELETE FROM utilisateurs WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);
            $stmt->execute();

            $_SESSION['mesgs']['confirm'][] = "Utilisateur supprimé avec succès.";
        } catch (Exception $e) {
            $_SESSION['mesgs']['errors'][] = "Erreur lors de la suppression de l'utilisateur : " . $e->getMessage();
        }
    }

    public function update(): void
    {
        if ($this->id === -1) {
            return;
        }

        try {
            // Si le mot de passe est explicitement fourni ou réinitialisé, on l'inclut dans la requête
            if (isset($this->password)) {
                $sql = "UPDATE utilisateurs SET username = :username, password = :password, admin = :admin, entite_id = :entite_id WHERE id = :id";
                $stmt = $this->db->prepare($sql);
                $stmt->bindValue(':password', $this->password, PDO::PARAM_STR);
            } else {
                $sql = "UPDATE utilisateurs SET username = :username, admin = :admin, entite_id = :entite_id WHERE id = :id";
                $stmt = $this->db->prepare($sql);
            }
            
            $stmt->bindValue(':username', $this->username, PDO::PARAM_STR);
            $stmt->bindValue(':admin', $this->admin, PDO::PARAM_BOOL);
            $stmt->bindValue(':entite_id', $this->entite_id, PDO::PARAM_INT);
            $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);
            $stmt->execute();

            $_SESSION['mesgs']['confirm'][] = "Utilisateur mis à jour avec succès.";
        } catch (Exception $e) {
            $_SESSION['mesgs']['errors'][] = "Erreur lors de la modification de l'utilisateur : " . $e->getMessage();
        }
    }

    public static function fetchAll(PDO $db): array
    {
        try {
            $sql = "SELECT id, username, password, admin, entite_id FROM utilisateurs ORDER BY username ASC";
            $stmt = $db->query($sql);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $utilisateurs = [];
            if ($result) {
                foreach ($result as $data) {
                    $utilisateurs[] = new Utilisateur($db, $data);
                }
            }

            return $utilisateurs;
        } catch (Exception $e) {
            $_SESSION['mesgs']['errors'][] = "Erreur lors de la récupération des utilisateurs : " . $e->getMessage();
            return [];
        }
    }
}
