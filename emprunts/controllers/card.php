<?php
$db = include(dirname(__FILE__) . '/../../lib/mypdo.php');
require_once(dirname(__FILE__) . '/../../class/emprunt.class.php');
require_once(dirname(__FILE__) . '/../../class/materiel.class.php');

if (isset($_POST["cancel"])) {
    header("Location: index.php?element=emprunts");
    exit(1);
}

if (!isset($_POST['id_emprunt']) || empty($_POST['id_emprunt'])) {
    $_SESSION['mesgs']['errors'][] = "ID de l'emprunt non spécifié.";
    header("Location: index.php?element=emprunts");
    exit(-1);
}

$id_emprunt = $_POST['id_emprunt'];
$emprunt = new Emprunt($db);
$emprunt->fetch($id_emprunt);

if (isset($_POST['update'])) {
    $emprunt->date_prevue_restitution = sanitize($_POST['date_prevue_restitution']) ?? $emprunt->date_prevue_restitution;

    if (isset($_POST['date_reelle_restitution']) && !empty($_POST['date_reelle_restitution'])) {
        $emprunt->date_reelle_restitution = sanitize($_POST['date_reelle_restitution']);
    } else {
        $emprunt->date_reelle_restitution = null;
    }

    $emprunt->caution = sanitize($_POST['caution']) ?? $emprunt->caution;
    $emprunt->remarque = sanitize($_POST['remarque']) ?? $emprunt->remarque;

    if ($emprunt->date_reelle_restitution) {
        $emprunt->etat_restitution = sanitize($_POST['etat_restitution']) ?? $emprunt->etat_restitution;
        $emprunt->remarque_restitution = sanitize($_POST['remarque_restitution']) ?? $emprunt->remarque_restitution;
    } else {
        $emprunt->etat_restitution = null;
        $emprunt->remarque_restitution = null;
    }

    $emprunt->update();

    $_SESSION['mesgs']['confirm'][] = "Emprunt mis à jour avec succès. - " . $emprunt->id_emprunt;
    header("Location: index.php?element=emprunts");
    exit;
}
