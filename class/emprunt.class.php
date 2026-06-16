<?php
class Emprunt
{
    private int $id_emprunt;
    private string $nom_emprunteur;
    private string $prenom_emprunteur;
    private int $id_groupe;
    private string $nom_groupe;
    private string $date_emprunt;
    private string $date_prevue_restitution;
    private ?string $date_reelle_restitution;
    private string $caution;
    private ?string $remarque;
    private ?string $etat_restitution;
    private ?string $remarque_restitution;
    private int $nombre_materiels;
    private int $nombre_materiels_rendus;
    private ?string $materiels_resume;
    private ?string $materiels_details;
    private ?string $materiels_assoc;
    private array $ids_materiels;
    private int $entite_id;

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
        $this->date_emprunt = '';
        $this->date_prevue_restitution = '';
        $this->date_reelle_restitution = null;
        $this->caution = '';
        $this->remarque = null;
        $this->etat_restitution = null;
        $this->remarque_restitution = null;
        $this->nombre_materiels = 0;
        $this->nombre_materiels_rendus = 0;
        $this->materiels_resume = null;
        $this->materiels_details = null;
        $this->materiels_assoc = null;
        $this->ids_materiels = [];
        $this->entite_id = -1;

        if (!empty($data)) {
            $this->hydrate($data);
        }
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
        $materiels = $this->materiels_resume ?? '';

        return $this->nom_emprunteur . ' ' . $this->prenom_emprunteur . ' (ID: ' . $this->id_emprunt . ')' .
            ($materiels !== '' ? ' - ' . $materiels : '');
    }

    public function hydrate(array $data): void
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key))
                $this->$key = $value;
        }
    }

    private function normaliserIdsMateriels(): array
    {
        $ids = [];

        foreach ($this->ids_materiels as $id_materiel) {
            $id_materiel = (int) $id_materiel;
            if ($id_materiel > 0) {
                $ids[$id_materiel] = $id_materiel;
            }
        }

        return array_values($ids);
    }

    public function create(): void
    {
        try {
            $ids_materiels = $this->normaliserIdsMateriels();

            if (empty($ids_materiels)) {
                throw new InvalidArgumentException('Aucun matériel sélectionné.');
            }

            $this->db->beginTransaction();

            $sql = 'SELECT create_emprunt(:nom_emprunteur, :prenom_emprunteur,
                  :id_groupe, :ids_materiels::int[], :date_emprunt, :date_prevue_restitution, :caution, :remarque, :entite_id) AS id_emprunt';
            $stmt = $this->db->prepare($sql);

            $stmt->bindValue(':nom_emprunteur', $this->nom_emprunteur, PDO::PARAM_STR);
            $stmt->bindValue(':prenom_emprunteur', $this->prenom_emprunteur, PDO::PARAM_STR);
            $stmt->bindValue(':id_groupe', $this->id_groupe, PDO::PARAM_INT);
            $stmt->bindValue(':ids_materiels', '{' . implode(',', $ids_materiels) . '}', PDO::PARAM_STR);
            $stmt->bindValue(':date_emprunt', $this->date_emprunt, PDO::PARAM_STR);
            $stmt->bindValue(':date_prevue_restitution', $this->date_prevue_restitution, PDO::PARAM_STR);
            $stmt->bindValue(':caution', $this->caution, PDO::PARAM_STR);
            $stmt->bindValue(':remarque', $this->remarque, $this->remarque === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $stmt->bindValue(':entite_id', $this->entite_id, PDO::PARAM_INT);

            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$result || !isset($result['id_emprunt'])) {
                throw new RuntimeException('Impossible de créer l’emprunt.');
            }

            $this->id_emprunt = (int) $result['id_emprunt'];

            $this->db->commit();
            $_SESSION['mesgs']['confirm'][] = "Emprunt créé avec succès ! ";
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();

            $_SESSION['mesgs']['errors'][] = "ERREUR Base de données : " . $e->getMessage();
        }
    }

    public function fetch(int $identifier): void
    {
        try {
            $fields = array(
                'id_emprunt',
                'nom_emprunteur',
                'prenom_emprunteur',
                'id_groupe',
                'nom_groupe',
                'date_emprunt',
                'date_prevue_restitution',
                'date_reelle_restitution',
                'caution',
                'remarque',
                'etat_restitution',
                'remarque_restitution',
                'nombre_materiels',
                'nombre_materiels_rendus',
                'materiels_resume',
                'materiels_details',
                'materiels_assoc',
                'entite_id'
            );

            $sql = 'SELECT ' . implode(', ', $fields) . ' FROM vw_emprunts_materiels WHERE id_emprunt = :id_emprunt';
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id_emprunt', $identifier, PDO::PARAM_INT);
            $stmt->execute();
            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($data)
                $this->hydrate($data);
        } catch (PDOException $e) {
            $_SESSION['mesgs']['errors'][] = "ERREUR lors de la récupération de l'emprunt : " . $e->getMessage();
        }
    }

    public function update(): void
    {
        try {
            $fields = array('id_emprunt', 'date_prevue_restitution', 'caution', 'remarque');

            $sql = 'SELECT update_emprunt(:' . implode(', :', $fields) . ')';
            $stmt = $this->db->prepare($sql);

            $stmt->bindValue(':id_emprunt', $this->id_emprunt, PDO::PARAM_INT);
            $stmt->bindValue(':date_prevue_restitution', $this->date_prevue_restitution, PDO::PARAM_STR);
            $stmt->bindValue(':caution', $this->caution, PDO::PARAM_STR);
            $stmt->bindValue(':remarque', $this->remarque, $this->remarque === null ? PDO::PARAM_NULL : PDO::PARAM_STR);

            $stmt->execute();
            $_SESSION['mesgs']['confirm'][] = "Emprunt mis à jour avec succès.";
        } catch (PDOException $e) {
            $_SESSION['mesgs']['errors'][] = "ERREUR lors de la mise à jour de l'emprunt : " . $e->getMessage();
        }
    }

    public function rendreMateriel(int $id_materiel, ?string $etat_restitution = null, ?string $remarque_restitution = null, ?string $date_reelle_restitution = null, bool $isAjax = false): void
    {
        try {
            $this->db->beginTransaction();

            $sql = 'SELECT rendre_materiel(:id_emprunt, :id_materiel, :etat_restitution, :remarque_restitution, :date_reelle_restitution) AS a_ete_rendu';
            $stmt = $this->db->prepare($sql);

            $stmt->bindValue(':id_emprunt', $this->id_emprunt, PDO::PARAM_INT);
            $stmt->bindValue(':id_materiel', $id_materiel, PDO::PARAM_INT);
            $stmt->bindValue(':etat_restitution', $etat_restitution, $etat_restitution === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $stmt->bindValue(':remarque_restitution', $remarque_restitution, $remarque_restitution === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $stmt->bindValue(':date_reelle_restitution', $date_reelle_restitution, $date_reelle_restitution === null ? PDO::PARAM_NULL : PDO::PARAM_STR);

            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            $this->db->commit();

            $this->fetch($this->id_emprunt);

            if (!$isAjax) {
                if ($result && $result['a_ete_rendu']) {
                    $_SESSION['mesgs']['confirm'][] = "Matériel rendu avec succès.";
                } else {
                    $_SESSION['mesgs']['errors'][] = "Le matériel a déjà été rendu ou n'appartient pas à cet emprunt.";
                }
            }
        } catch (PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            if (!$isAjax) {
                $_SESSION['mesgs']['errors'][] = "ERREUR lors du rendu du matériel : " . $e->getMessage();
            } else {
                throw $e;
            }
        }
    }

    public function fetchMateriels(): array
    {
        try {
            $fields = array('id_materiel', 'nom_materiel', 'modele_materiel', 'etiquette_ulco_materiel', 'etat_materiel', 'date_reelle_restitution', 'etat_restitution', 'remarque_restitution');
            $sql = 'SELECT ' . implode(', ', $fields) . ' FROM vw_emprunt_materiels WHERE id_emprunt = :id_emprunt ORDER BY id_materiel';
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id_emprunt', $this->id_emprunt, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $_SESSION['mesgs']['errors'][] = "ERREUR lors de la récupération des matériaux de l'emprunt " . $this->id_emprunt . " : " . $e->getMessage();
            return [];
        }
    }

    public static function fetchAll(PDO $db): array
    {
        try {
            $fields = array(
                'id_emprunt',
                'nom_emprunteur',
                'prenom_emprunteur',
                'id_groupe',
                'nom_groupe',
                'date_emprunt',
                'date_prevue_restitution',
                'date_reelle_restitution',
                'caution',
                'remarque',
                'etat_restitution',
                'remarque_restitution',
                'nombre_materiels',
                'nombre_materiels_rendus',
                'materiels_resume',
                'materiels_details',
                'materiels_assoc',
                'entite_id'
            );
            
            $sql = 'SELECT ' . implode(', ', $fields) . ' FROM vw_emprunts_materiels';

            $isNotAdmin = (empty($_SESSION['user']['admin']) || $_SESSION['user']['admin'] !== true);
            if ($isNotAdmin) {
                $sql .= ' WHERE entite_id = :entite_id';
            }

            $sql .= ' ORDER BY id_emprunt DESC';
            
            $stmt = $db->prepare($sql);

            if ($isNotAdmin) {
                $stmt->bindValue(':entite_id', $_SESSION['user']['entite_id'] ?? 0, PDO::PARAM_INT);
            }

            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $emprunts = [];
            if ($result) {
                foreach ($result as $data) {
                    $emprunts[] = new Emprunt($db, $data);
                }
            }

            return $emprunts;
        } catch (PDOException $e) {
            $_SESSION['mesgs']['errors'][] = "ERREUR lors de la récupération des emprunts : " . $e->getMessage();
            return [];
        }
    }

    public static function fetchAllByMaterielId(PDO $db, int $id_materiel): array
    {
        try {
            $sql = 'SELECT 
                        id_emprunt, 
                        nom_emprunteur, 
                        prenom_emprunteur, 
                        nom_groupe, 
                        date_emprunt, 
                        date_prevue_restitution, 
                        date_reelle_restitution, 
                        remarque_restitution, 
                        etat_restitution 
                    FROM get_emprunts_by_materiel(:id_materiel)';

            $isNotAdmin = (empty($_SESSION['user']['admin']) || $_SESSION['user']['admin'] !== true);
            if ($isNotAdmin) {
                $sql .= ' WHERE entite_id = :entite_id';
            }

            $stmt = $db->prepare($sql);
            $stmt->bindValue(':id_materiel', $id_materiel, PDO::PARAM_INT);

            if ($isNotAdmin) {
                $stmt->bindValue(':entite_id', $_SESSION['user']['entite_id'] ?? 0, PDO::PARAM_INT);
            }

            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $emprunts = [];
            if ($result) {
                foreach ($result as $data)
                    $emprunts[] = new Emprunt($db, $data);
            }

            return $emprunts;
        } catch (PDOException $e) {
            $_SESSION['mesgs']['errors'][] = "ERREUR lors de la récupération des emprunts par matériel " . $id_materiel . " : " . $e->getMessage();
            return [];
        }
    }

    public function getMaterielsAssoc(): array
    {
        $materiels = [];
        if (!empty($this->materiels_assoc)) {
            $parts = explode('||', $this->materiels_assoc);
            foreach ($parts as $part) {
                $subparts = explode(':', $part, 2);
                if (count($subparts) === 2) {
                    $materiels[(int)$subparts[0]] = $subparts[1];
                }
            }
        }
        return $materiels;
    }
}
