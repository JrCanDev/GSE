<?php
$db = include(dirname(__FILE__) . '/../../lib/mypdo.php');
require_once(dirname(__FILE__) . '/../../class/groupe.class.php');

$groupes = Groupe::fetchAll($db);

$groupesActifs = [];
$groupesPasVisible = [];

foreach ($groupes as $g) {
    if ($g->est_affiche) {
        $groupesActifs[] = $g;
    } else {
        $groupesPasVisible[] = $g;
    }
}

if (isset($_POST["toggle_state"]) && isset($_POST["id"]) && !empty($_POST["id"])) {
    $rowid = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);
    $groupe = new Groupe($db);
    $groupe->fetch($rowid);

    $groupe->est_affiche = !$groupe->est_affiche;
    $groupe->update();

    $_SESSION['mesgs']['confirm'][] = "État du groupe mis à jour avec succès.";

    header("Location: index.php?element=groupes");
    exit(1);
}