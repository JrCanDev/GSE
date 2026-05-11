<?php
$db = include(dirname(__FILE__) . '/../../lib/mypdo.php');
require_once(dirname(__FILE__) . '/../../class/emprunt.class.php');

$emprunts = Emprunt::fetchAll($db);

if (isset($_POST["submit_date"]) && isset($_POST["id_emprunt"]) && !empty($_POST["id_emprunt"])) {
    $id_emprunt = filter_input(INPUT_POST, "id_emprunt", FILTER_VALIDATE_INT);
    $emprunt = new Emprunt($db);
    $emprunt->fetch($id_emprunt);

    $emprunt->date_reelle_restitution = date('Y-m-d');
    
    $emprunt->update();
    $_SESSION['mesgs']['confirm'][] = "Emprunt mis à jour avec succès.";

    header("Location: index.php?element=emprunts");
    exit(1);
}

usort($emprunts, function($a, $b) {
    // trier par le nom de l'année et après par nom de famille
    $cmp = strcmp($b->nom_groupe, $a->nom_groupe);
    if ($cmp !== 0) {
        return $cmp;
    }
    
    return strcmp($a->nom_emprunteur, $b->nom_emprunteur);
});