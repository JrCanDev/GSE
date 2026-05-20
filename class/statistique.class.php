<?php
class Statistiques {

    public static function getComputerStatisticsByYear(PDO $db) {
        $sql = "SELECT * FROM vw_stats_ordinateurs_par_annee";
        $stmt = $db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }   

}