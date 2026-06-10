<?php
class Statistique
{
    public static function getStatisticsByYear(PDO $db, string $recherche = ''): array
    {
        try {
            $sql = "SELECT annee_universitaire, nom_groupe, nombre_prets 
                    FROM stats_materiels_filtre(:search, :entite_id)";
            
            $stmt = $db->prepare($sql);
            
            // On lie la recherche textuelle
            $stmt->bindValue(':search', $recherche, PDO::PARAM_STR);
            
            $isSuperAdmin = (!empty($_SESSION['user']['admin']) && $_SESSION['user']['admin'] === true);
            $entiteId = $isSuperAdmin ? null : ($_SESSION['user']['entite_id'] ?? null);

            if ($entiteId === null) {
                $stmt->bindValue(':entite_id', null, PDO::PARAM_NULL);
            } else {
                $stmt->bindValue(':entite_id', (int)$entiteId, PDO::PARAM_INT);
            }
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $_SESSION['mesgs']['errors'][] = "ERREUR lors de la récupération des statistiques : " . $e->getMessage();
            return [];
        }
    }
}