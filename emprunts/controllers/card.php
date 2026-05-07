<?php
require_once(dirname(__FILE__) . '/../../lib/mypdo.php');
require_once(dirname(__FILE__) . '/../../class/etudiant.class.php');

if (!isset($_POST['numetu']) || empty($_POST['numetu'])) {
    $_SESSION['mesgs']['errors'][] = "Numéro étudiant non spécifié.";
    header("Location: " . $_SERVER['PHP_SELF'] . "?element=etudiants&action=list");
    exit;
}

$numetu = $_POST['numetu'];
$etudiant = new Etudiant($db);
$etudiant->fetch($numetu);

if (isset($_POST['update'])) {
    $etudiant->numetu    = $_POST['numetu'] ?? $etudiant->numetu;
    $etudiant->firstname = $_POST['firstname'] ?? $etudiant->firstname;
    $etudiant->lastname  = $_POST['lastname'] ?? $etudiant->lastname;
    $etudiant->birthday  = $_POST['birthday'] ?? $etudiant->birthday;
    $etudiant->year      = (int) ($_POST['year'] ?? $etudiant->year);
    $etudiant->diploma   = $_POST['diploma'] ?? $etudiant->diploma;
    $etudiant->td        = $_POST['td'] ?? $etudiant->td;
    $etudiant->tp        = $_POST['tp'] ?? $etudiant->tp;
    $etudiant->address   = $_POST['address'] ?? $etudiant->address;
    $etudiant->zipcode   = $_POST['zipcode'] ?? $etudiant->zipcode;
    $etudiant->town      = $_POST['town'] ?? $etudiant->town;

    $etudiant->update();

    $_SESSION['mesgs']['confirm'][] = "Étudiant mis à jour avec succès.";
    header("Location: " . $_SERVER['PHP_SELF'] . "?element=etudiants&action=list");
    exit;
}

// Récupérer les classements de l'étudiant dans chaque module
$classements = $etudiant->getClassementsParModule();