<?php
$db = include(dirname(__FILE__) . '/../../lib/mypdo.php');
require_once(dirname(__FILE__) . '/../../class/materiel.class.php');
require_once(dirname(__FILE__) . '/../../class/emprunt.class.php');

$old_page = $_POST['old_page'] ?? 'materiels';

if (isset($_POST["cancel"])) {
    header("Location: index.php?element=" . $old_page);
    exit(1);
}

if (!isset($_POST['id_materiel']) || empty($_POST['id_materiel'])) {
    $_SESSION['mesgs']['errors'][] = "ID du matériel non spécifié.";
    header("Location: " . $_SERVER['PHP_SELF'] . "?element=" . $old_page);
    exit(-1);
}

$id_materiel = $_POST['id_materiel'];
$materiel = new Materiel($db);
$materiel->fetch($id_materiel);

$emprunts = Emprunt::fetchAllByMaterielId($db, $id_materiel);

if (isset($_POST['update'])) {
    $materiel->nom = sanitize($_POST['nom']) ?? $materiel->nom;
    $materiel->modele = sanitize($_POST['modele']) ?? $materiel->modele;
    $materiel->annee = sanitize($_POST['annee']) ?? $materiel->annee;
    $materiel->etiquette_ulco = sanitize($_POST['etiquette_ulco']) ?? $materiel->etiquette_ulco;
    $materiel->etat = sanitize($_POST['etat']) ?? $materiel->etat;
    $materiel->localisation = sanitize($_POST['localisation']) ?? $materiel->localisation;
    $materiel->descriptif = sanitize($_POST['descriptif']) ?? $materiel->descriptif;
    $materiel->remarque = sanitize($_POST['remarque']) ?? $materiel->remarque;

    $materiel->update();

    $_SESSION['mesgs']['confirm'][] = "Matériel mis à jour avec succès.";
    header("Location: index.php?element=" . $old_page);
    exit;
}