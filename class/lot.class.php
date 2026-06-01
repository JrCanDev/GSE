<?php
require_once(dirname(__FILE__) . '/materiel.class.php');
class Lot
{
    public int $id_lot;
    public string $nom_lot;

    private PDO $db;

    public function __construct(PDO $db, array $data = [])
    {
        $this->db = $db;

        $this->id_lot = -1;
        $this->nom_lot = '';

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
        return $this->nom_lot . ' (ID: ' . $this->id_lot . ')';
    }

    public function hydrate(array $data): void
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key))
                $this->$key = $value;
        }
    }

    public function create(array $ids_materiels = []): void
    {
        try {
            $this->db->beginTransaction();

            $sql = "INSERT INTO lots (nom_lot) VALUES (:nom_lot)";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':nom_lot', $this->nom_lot, PDO::PARAM_STR);
            $stmt->execute();

            $this->id_lot = (int) $this->db->lastInsertId();

            $this->saveMateriels($ids_materiels);

            $this->db->commit();

            $_SESSION['mesgs']['confirm'][] = "Lot créé avec succès.";
        } catch (Exception $e) {
            $this->db->rollBack();
            $_SESSION['mesgs']['errors'][] = "Erreur lors de la création du lot : " . $e->getMessage();
        }
    }

    public function fetch(int $id_lot): void
    {
        try {
            $fields = array('id_lot', 'nom_lot');
            $sql = "SELECT " . implode(', ', $fields) . " FROM lots WHERE id_lot = :id_lot";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id_lot', $id_lot, PDO::PARAM_INT);
            $stmt->execute();
            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($data)
                $this->hydrate($data);
        } catch (Exception $e) {
            $_SESSION['mesgs']['errors'][] = "Erreur lors de la récupération du lot : " . $e->getMessage();
        }
    }

    public function fetchMaterielsIds(): array
    {
        try {
            $sql = "SELECT id_materiel FROM lot_materiels WHERE id_lot = :id_lot";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id_lot', $this->id_lot, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $lots = [];
            if ($result) {
                foreach ($result as $data) {
                    $materiel = new Materiel($this->db);
                    $materiel->fetch((int) $data['id_materiel']);
                    $lots[] = $materiel;
                }
            }

            return $lots;
        } catch (Exception $e) {
            $_SESSION['mesgs']['errors'][] = "Erreur lors de la récupération des matériels du lot : " . $e->getMessage();
            return [];
        }
    }

    public function delete(): void
    {
        if ($this->id_lot === -1) return;

        try {
            $sql = "DELETE FROM lots WHERE id_lot = :id_lot";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id_lot', $this->id_lot, PDO::PARAM_INT);
            $stmt->execute();

            $_SESSION['mesgs']['confirm'][] = "Lot supprimé avec succès.";
        } catch (Exception $e) {
            $_SESSION['mesgs']['errors'][] = "Erreur lors de la suppression du lot : " . $e->getMessage();
        }
    }

    public function update(array $ids_materiels = []): void
    {
        if ($this->id_lot === -1) return;

        try {
            $this->db->beginTransaction();

            $sql = "UPDATE lots SET nom_lot = :nom_lot WHERE id_lot = :id_lot";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':nom_lot', $this->nom_lot, PDO::PARAM_STR);
            $stmt->bindValue(':id_lot', $this->id_lot, PDO::PARAM_INT);
            $stmt->execute();

            $this->saveMateriels($ids_materiels);

            $this->db->commit();

            $_SESSION['mesgs']['confirm'][] = "Lot mis à jour avec succès.";
        } catch (Exception $e) {
            $this->db->rollBack();
            $_SESSION['mesgs']['errors'][] = "Erreur lors de la modification du lot : " . $e->getMessage();
        }
    }

    public static function fetchAll(PDO $db): array
    {
        try {
            $sql = "SELECT id_lot, nom_lot FROM lots ORDER BY nom_lot ASC";
            $stmt = $db->query($sql);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $lots = [];
            if ($result) {
                foreach ($result as $data) {
                    $lots[] = new Lot($db, $data);
                }
            }

            return $lots;
        } catch (Exception $e) {
            $_SESSION['mesgs']['errors'][] = "Erreur lors de la récupération des lots : " . $e->getMessage();
            return [];
        }
    }

    private function saveMateriels(array $ids_materiels): void
    {
        // on enlève les anciens matériels
        $sqlDelete = "DELETE FROM lot_materiels WHERE id_lot = :id_lot";
        $stmtDelete = $this->db->prepare($sqlDelete);
        $stmtDelete->bindValue(':id_lot', $this->id_lot, PDO::PARAM_INT);
        $stmtDelete->execute();

        if (empty($ids_materiels)) return;

        // on insère les nouveaux matériels
        $sqlInsert = "INSERT INTO lot_materiels (id_lot, id_materiel) VALUES (:id_lot, :id_materiel)";
        $stmtInsert = $this->db->prepare($sqlInsert);

        foreach ($ids_materiels as $id_materiel) {
            $stmtInsert->bindValue(':id_lot', $this->id_lot, PDO::PARAM_INT);
            $stmtInsert->bindValue(':id_materiel', (int)$id_materiel, PDO::PARAM_INT);
            $stmtInsert->execute();
        }
    }
}
