<?php
session_start();
require_once(dirname(__FILE__) . '/../../lib/mypdo.php');
require_once(dirname(__FILE__) . '/../../class/emprunt.class.php');

if (isset($_POST["cancel"])) {
    header("Location: " . $_SERVER['PHP_SELF'] . "?element=emprunts");
    exit(1);
}

if (isset($_POST["submit"])) {
    $data = filter_input_array(INPUT_POST, [
        "firstname" => FILTER_UNSAFE_RAW,
        "lastname" => FILTER_UNSAFE_RAW,
        "year" => FILTER_UNSAFE_RAW,
        "caution" => FILTER_UNSAFE_RAW,
        "date_emprunt" => FILTER_UNSAFE_RAW,
        "date_restitution_prevue" => FILTER_UNSAFE_RAW,
        "remarque" => FILTER_UNSAFE_RAW
    ]);

    $ids_choisis = array_filter($_POST['materiel_selectionne'], function ($val) {
        return $val !== "-1";
    });

    if (!empty($ids_choisis)) {
        echo "IDs des matériels à traiter : " . implode(", ", $ids_choisis);
    } else {
        echo "Rien n'a été sélectionné.";
    }

    foreach ($data as $key => $value) {
        if (is_string($value)) $data[$key] = htmlspecialchars($value);
    }

    $_SESSION['mesgs']['confirm'][] = "Emprunt créé avec succès ! ";

    header("Location: " . $_SERVER['PHP_SELF'] . "?element=emprunts");
    exit(1);
}

$pageTitle = "Ajouter un emprunt";

// TODO à remplacer quand BDD sera prête
$years = array('BUT 1', 'BUT 2 FI', 'BUT 2 APP', 'BUT 3 FI', 'BUT 3 APP', 'Autre');

$materiels = [
    ["id" => 10755, "nom" => "PC Port. DELL", "modele" => "Modèle 2026", "disponible" => true],
    ["id" => 10756, "nom" => "PC Port. DELL", "modele" => "Modèle 2026", "disponible" => true],
    ["id" => 10757, "nom" => "PC Port. DELL", "modele" => "Modèle 2025", "disponible" => true],
    ["id" => 10758, "nom" => "PC Port. DELL", "modele" => "Modèle 2025", "disponible" => true],
    ["id" => 10759, "nom" => "PC Port. DELL", "modele" => "Modèle 2025", "disponible" => true],
    ["id" => 10760, "nom" => "PC Port. DELL", "modele" => "Modèle 2025", "disponible" => true],
    ["id" => 10761, "nom" => "PC Port. DELL", "modele" => "Modèle 2024", "disponible" => true],
    ["id" => 10762, "nom" => "PC Port. DELL", "modele" => "Modèle 2025", "disponible" => false],
    ["id" => 10763, "nom" => "PC Port. DELL", "modele" => "Modèle 2025", "disponible" => false],
    ["id" => 10764, "nom" => "PC Port. DELL", "modele" => "Modèle 2025", "disponible" => false],
    ["id" => 10765, "nom" => "PC Port. DELL", "modele" => "Modèle 2025", "disponible" => false],
    ["id" => 10766, "nom" => "PC Port. DELL", "modele" => "Modèle 2025", "disponible" => false],
    ["id" => 10767, "nom" => "PC Port. DELL", "modele" => "Modèle 2025", "disponible" => false],
    ["id" => 10768, "nom" => "PC Port. DELL", "modele" => "Modèle 2025", "disponible" => false],
    ["id" => 10769, "nom" => "PC Port. DELL", "modele" => "Modèle 2025", "disponible" => false],
];

usort($materiels, function($a, $b) {
    if ($a['disponible'] !== $b['disponible']) {
        return $b['disponible'] <=> $a['disponible'];
    }

    $anneeA = (int) filter_var($a['modele'], FILTER_SANITIZE_NUMBER_INT);
    $anneeB = (int) filter_var($b['modele'], FILTER_SANITIZE_NUMBER_INT);

    return $anneeB <=> $anneeA;
});