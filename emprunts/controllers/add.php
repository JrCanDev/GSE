<?php
session_start();
$db = include(dirname(__FILE__) . '/../../lib/mypdo.php');
require_once(dirname(__FILE__) . '/../../class/emprunt.class.php');
require_once(dirname(__FILE__) . '/../../class/groupe.class.php');
require_once(dirname(__FILE__) . '/../../class/lot.class.php');
require_once(dirname(__FILE__) . '/../../class/materiel.class.php');
require_once(dirname(__FILE__) . '/../../lib/myproject.lib.php');

if (!isUserAdmin()) {
    include dirname(__FILE__) . '/../../login.php';
    exit(1);
}

if (isset($_POST["cancel"])) {
    header("Location: " . $_SERVER['PHP_SELF'] . "?element=emprunts");
    exit(1);
}

if (isset($_POST["submit"])) {
    $data = filter_input_array(INPUT_POST, [
        "nom_emprunteur" => FILTER_UNSAFE_RAW,
        "prenom_emprunteur" => FILTER_UNSAFE_RAW,
        "id_groupe" => FILTER_VALIDATE_INT,
        "date_emprunt" => FILTER_UNSAFE_RAW,
        "date_prevue_restitution" => FILTER_UNSAFE_RAW,
        "caution" => FILTER_UNSAFE_RAW,
        "remarque" => FILTER_UNSAFE_RAW,
    ]);

    foreach ($data as $key => $value) {
        if (is_string($value)) $data[$key] = sanitize($value);
    }

    if (!in_array($data['caution'] ?? null, Emprunt::$cautions, true)) {
        $_SESSION['mesgs']['errors'][] = "Veuillez choisir une caution valide.";
        header("Location: " . $_SERVER['PHP_SELF'] . "?element=emprunts&action=add");
        exit(1);
    }

    $ids_materiels = $_POST['ids_materiels'] ?? [];
    if (!is_array($ids_materiels)) {
        $ids_materiels = [$ids_materiels];
    }

    $ids_materiels = array_values(array_unique(array_filter(array_map('intval', $ids_materiels), function ($id_materiel) {
        return $id_materiel > 0;
    })));

    if (empty($ids_materiels)) {
        $_SESSION['mesgs']['errors'][] = "Veuillez sélectionner au moins un matériel.";
        header("Location: " . $_SERVER['PHP_SELF'] . "?element=emprunts&action=add");
        exit(1);
    }

    foreach ($ids_materiels as $id_materiel) {
        if (!Materiel::estDisponible($db, $id_materiel)) {
            $_SESSION['mesgs']['errors'][] = "Le matériel #" . $id_materiel . " n'est plus disponible.";
            header("Location: " . $_SERVER['PHP_SELF'] . "?element=emprunts&action=add");
            exit(1);
        }
    }

    $data['ids_materiels'] = $ids_materiels;

    $emprunt = new Emprunt($db, $data);
    $emprunt->create();

    header("Location: " . $_SERVER['PHP_SELF'] . "?element=emprunts");
    exit(1);
}

$years = Groupe::fetchAll($db);
$materiels = Materiel::fetchAll($db);

$lots = Lot::fetchAll($db);
$lotsData = [];

foreach ($lots as $lotObj) {
    $materielsObjets = $lotObj->fetchMaterielsIds();
    $idsUniquement = array_map(function ($m) {
        return (int) $m->id_materiel;
    }, $materielsObjets);

    $lotsData[] = [
        'id_lot' => $lotObj->id_lot,
        'nom_lot' => $lotObj->nom_lot,
        'ids_materiels' => $idsUniquement
    ];
}

usort($materiels, function ($a, $b) {
    $dispoA = $a->disponible ? 1 : 0;
    $dispoB = $b->disponible ? 1 : 0;

    if ($dispoA !== $dispoB) {
        return $dispoB <=> $dispoA;
    }

    return $a->id_materiel <=> $b->id_materiel;
});
