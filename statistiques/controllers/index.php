<?php
$db = include(dirname(__FILE__) . '/../../lib/mypdo.php');
require_once(dirname(__FILE__) . '/../../class/statistique.class.php');

$stats = Statistiques::getComputerStatisticsByYear($db);

$fluxParAnnee = [];
foreach ($stats as $ligne) {
    $fluxParAnnee[$ligne['annee_universitaire']][] = $ligne;
}