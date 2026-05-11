<?php
$db = include(dirname(__FILE__) . '/../../lib/mypdo.php');
require_once(dirname(__FILE__) . '/../../class/materiel.class.php');

$materiels = Materiel::fetchAll($db);

if (isset($_POST["delete"]) && isset($_POST["id"]) && !empty($_POST["id"])) {
    $rowid = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);
    $materiel = new Materiel($db);
    $materiel->fetch($rowid);

    $materiel->delete();
    $_SESSION['mesgs']['confirm'][] = "Matériel supprimé avec succès.";

    header("Location: index.php?element=materiels");
    exit(1);
}