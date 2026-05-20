<?php
$db = include(dirname(__FILE__) . '/../../lib/mypdo.php');
require_once(dirname(__FILE__) . '/../../class/statistique.class.php');

$stats = Statistiques::getComputerStatisticsByYear($db);

$fluxParAnnee = [];
foreach ($stats as $ligne) {
    $fluxParAnnee[$ligne['annee_universitaire']][] = $ligne;
}

if (empty($fluxParAnnee)) {
    $listeAnnees = [];
    $matriceData = [];
} else {
    // on extrait la liste des années universitaires (triée) pour construire l'axe X du graphique
    $listeAnnees = array_keys($fluxParAnnee);
    sort($listeAnnees);

    $matriceData = [];
    $totauxParAnnee = array_fill_keys($listeAnnees, 0);
    $totalGeneral = 0;

    foreach ($fluxParAnnee as $annee => $groupes) {
        foreach ($groupes as $g) {
            $nomGroupe = $g['nom_groupe'];
            $nbPrets = $g['nombre_prets'];

            $matriceData[$nomGroupe][$annee] = $nbPrets;
            $totauxParAnnee[$annee] += $nbPrets;
            $totalGeneral += $nbPrets;
        }
    }

    ksort($matriceData);
}
