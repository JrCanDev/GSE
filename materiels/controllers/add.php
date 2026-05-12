<?php
session_start();
$db = include(dirname(__FILE__) . '/../../lib/mypdo.php');
require_once(dirname(__FILE__) . '/../../class/materiel.class.php');
require_once(dirname(__FILE__) . '/../../class/emprunt.class.php');

if (isset($_POST["cancel"])) {
    header("Location: " . $_SERVER['PHP_SELF'] . "?element=materiels");
    exit(1);
}

if (isset($_POST["submit"])) {
    $data = filter_input_array(INPUT_POST, [
        "nom" => FILTER_UNSAFE_RAW,
        "modele" => FILTER_UNSAFE_RAW,
        "annee" => FILTER_VALIDATE_INT,
        "etiquette_ulco" => FILTER_UNSAFE_RAW,
        "etat" => FILTER_UNSAFE_RAW,
        "localisation" => FILTER_UNSAFE_RAW,
        "remarque" => FILTER_UNSAFE_RAW,
        "descriptif" => FILTER_UNSAFE_RAW,
    ]);

    foreach ($data as $key => $value) {
        if (is_string($value)) $data[$key] = sanitize($value);
    }

    $materiel = new Materiel($db, $data);
    $materiel->create();

    $_SESSION['mesgs']['confirm'][] = "Matériel créé avec succès ! ";

    header("Location: " . $_SERVER['PHP_SELF'] . "?element=materiels");
    exit(1);
}