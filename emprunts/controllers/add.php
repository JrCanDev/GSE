<?php
session_start();
$db = include(dirname(__FILE__) . '/../../lib/mypdo.php');
require_once(dirname(__FILE__) . '/../../class/emprunt.class.php');
require_once(dirname(__FILE__) . '/../../class/groupe.class.php');
require_once(dirname(__FILE__) . '/../../class/materiel.class.php');

if (isset($_POST["cancel"])) {
    header("Location: " . $_SERVER['PHP_SELF'] . "?element=emprunts");
    exit(1);
}

if (isset($_POST["submit"])) {
    $data = filter_input_array(INPUT_POST, [
        "nom_emprunteur" => FILTER_UNSAFE_RAW,
        "prenom_emprunteur" => FILTER_UNSAFE_RAW,
        "id_groupe" => FILTER_VALIDATE_INT,
        "id_materiel" => FILTER_VALIDATE_INT,
        "date_emprunt" => FILTER_UNSAFE_RAW,
        "date_prevue_restitution" => FILTER_UNSAFE_RAW,
        "caution" => FILTER_UNSAFE_RAW,
        "remarque" => FILTER_UNSAFE_RAW,
    ]);

    foreach ($data as $key => $value) {
        if (is_string($value)) $data[$key] = sanitize($value);
    }

    $emprunt = new Emprunt($db, $data);
    $emprunt->create();

    $_SESSION['mesgs']['confirm'][] = "Emprunt créé avec succès ! ";

    header("Location: " . $_SERVER['PHP_SELF'] . "?element=emprunts");
    exit(1);
}

$years = Groupe::fetchAll($db);
$materiels = Materiel::fetchAll($db);

// TODO à refaire
// usort($materiels, function ($a, $b) {
//     if ($a['disponible'] !== $b['disponible']) {
//         return $b['disponible'] <=> $a['disponible'];
//     }

//     $anneeA = (int) filter_var($a['modele'], FILTER_SANITIZE_NUMBER_INT);
//     $anneeB = (int) filter_var($b['modele'], FILTER_SANITIZE_NUMBER_INT);

//     return $anneeB <=> $anneeA;
// });
