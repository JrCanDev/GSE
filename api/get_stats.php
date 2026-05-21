<?php
header('Content-Type: application/json');
$db = include(dirname(__FILE__) . '/../lib/mypdo.php');
require_once(dirname(__FILE__) . '/../class/statistique.class.php');

$recherche = isset($_GET['search']) ? trim($_GET['search']) : '';

$stats = Statistique::getStatisticsByYear($db, $recherche);

echo json_encode($stats);