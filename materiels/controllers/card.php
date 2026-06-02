<?php
$db = include(dirname(__FILE__) . '/../../lib/mypdo.php');
require_once(dirname(__FILE__) . '/../../lib/myproject.lib.php');
if (!isUserAdmin()) {
    include dirname(__FILE__) . '/../../login.php';
    exit(1);
}
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
    $materiel->nom = isset($_POST['nom']) ? trim($_POST['nom']) : $materiel->nom;
    $materiel->modele = isset($_POST['modele']) ? trim($_POST['modele']) : $materiel->modele;
    $materiel->annee = (!empty($_POST['annee'])) ? intval($_POST['annee']) : null;
    $materiel->etiquette_ulco = isset($_POST['etiquette_ulco']) ? trim($_POST['etiquette_ulco']) : $materiel->etiquette_ulco;
    $materiel->etat = isset($_POST['etat']) ? trim($_POST['etat']) : $materiel->etat;
    $materiel->localisation = isset($_POST['localisation']) ? trim($_POST['localisation']) : $materiel->localisation;
    $materiel->descriptif = isset($_POST['descriptif']) ? trim($_POST['descriptif']) : $materiel->descriptif;

    $materiel->remarque = (!empty($_POST['remarque'])) ? trim($_POST['remarque']) : null;

    $materiel->update();

    header("Location: index.php?element=" . $old_page);
    exit;
}
