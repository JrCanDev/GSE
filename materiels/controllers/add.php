<?php
session_start();
$db = include(dirname(__FILE__) . '/../../lib/mypdo.php');
require_once(dirname(__FILE__) . '/../../lib/myproject.lib.php');
require_once(dirname(__FILE__) . '/../../class/materiel.class.php');
require_once(dirname(__FILE__) . '/../../class/emprunt.class.php');

if (!isUserLoggedIn()) {
    header('location: index.php');
    exit(1);
}

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
        if (is_string($value)) {
            $data[$key] = trim($value);
        }
    }

    if ($data['annee'] === false || $data['annee'] === null || $data['annee'] === '') {
        $data['annee'] = null;
    }

    if (empty($data['etiquette_ulco'])) {
        $data['etiquette_ulco'] = null;
    }

    if (!empty($data['etiquette_ulco'])) {
        $existing = Materiel::fetchByEtiquetteUlco($db, $data['etiquette_ulco']);
        if ($existing) {
            $label = $existing['nom'] ?? 'Matériel';
            $modele = trim((string)($existing['modele'] ?? ''));
            if ($modele !== '') {
                $label .= ' | ' . $modele;
            }
            $id = $existing['id_materiel'] ?? null;
            $_SESSION['mesgs']['errors'][] = $id
                ? "Cet identifiant est déjà utilisé par le matériel #$id ($label)."
                : "Cet identifiant est déjà utilisé par un autre matériel.";
            header("Location: " . $_SERVER['PHP_SELF'] . "?element=materiels&action=add");
            exit(1);
        }
    }

    $data['entite_id'] = intval($_SESSION['user']['entite_id'] ?? 0);

    $materiel = new Materiel($db, $data);
    $materiel->create();

    header("Location: " . $_SERVER['PHP_SELF'] . "?element=materiels");
    exit(1);
}