<?php
$db = include(dirname(__FILE__) . '/../../lib/mypdo.php');
require_once(dirname(__FILE__) . '/../../class/statistique.class.php');

$rechercheInitiale = 'PC';
$stats = Statistique::getStatisticsByYear($db, $rechercheInitiale);

$fluxParAnnee = [];
foreach ($stats as $ligne) {
    $fluxParAnnee[$ligne['annee_universitaire']][] = $ligne;
}

if (empty($fluxParAnnee)) {
    $listeAnnees = [];
    $matriceData = [];
} else {
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