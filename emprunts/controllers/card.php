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
$materiels = $emprunt->fetchMateriels();

if (isset($_POST['return_material'])) {
    $id_materiel = filter_input(INPUT_POST, 'id_materiel', FILTER_VALIDATE_INT);
    if (!$id_materiel) {
        $_SESSION['mesgs']['errors'][] = "ID du matériel non spécifié.";
        header("Location: index.php?element=emprunts");
        exit(-1);
    }

    $etat_restitution = sanitize($_POST['etat_restitution'] ?? '');
    if (!in_array($etat_restitution, Materiel::$etats, true)) {
        $etat_restitution = null;
    }

    $remarque_restitution = html_entity_decode($_POST['remarque_restitution'] ?? '', ENT_QUOTES, 'UTF-8');
    if ($remarque_restitution === '') {
        $remarque_restitution = null;
    }

    $emprunt->rendreMateriel($id_materiel, $etat_restitution, $remarque_restitution, date('Y-m-d'));

    header("Location: index.php?element=emprunts");
    exit;
}

if (isset($_POST['update'])) {
    $emprunt->date_prevue_restitution = sanitize($_POST['date_prevue_restitution']) ?? $emprunt->date_prevue_restitution;

    $caution = sanitize($_POST['caution'] ?? null);
    if (in_array($caution, Emprunt::$cautions, true)) {
        $emprunt->caution = $caution;
    }

    $emprunt->remarque = html_entity_decode($_POST['remarque'], ENT_QUOTES, 'UTF-8') ?? $emprunt->remarque;

    $emprunt->update();
    
    header("Location: index.php?element=emprunts");
    exit;
}
