<?php
$db = include(dirname(__FILE__) . '/../../lib/mypdo.php');
require_once(dirname(__FILE__) . '/../../class/groupe.class.php');

if (isset($_POST["cancel"])) {
    header("Location: index.php?element=groupes");
    exit(1);
}

if (!isset($_POST['id_groupe']) || empty($_POST['id_groupe'])) {
    $_SESSION['mesgs']['errors'][] = "ID du groupe non spécifié.";
    header("Location: " . $_SERVER['PHP_SELF'] . "?element=groupes");
    exit(-1);
}

$id_groupe = $_POST['id_groupe'];
$groupe = new Groupe($db);
$groupe->fetch($id_groupe);

if (isset($_POST['update'])) {
    $groupe->nom_groupe = sanitize($_POST['nom_groupe']) ?? $groupe->nom_groupe;
    $groupe->date_restitution = sanitize($_POST['date_restitution']) ?? $groupe->date_restitution;

    $groupe->update();

    $_SESSION['mesgs']['confirm'][] = "Groupe mis à jour avec succès.";
    header("Location: index.php?element=groupes");
    exit;
}