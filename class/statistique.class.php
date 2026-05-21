<?php
class Statistique
{
    public static function getStatisticsByYear(PDO $db, string $recherche = ''): array
    {
        $sql = "SELECT annee_universitaire, nom_groupe, nombre_prets FROM stats_materiels_filtre(:search)";
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':search', $recherche, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
