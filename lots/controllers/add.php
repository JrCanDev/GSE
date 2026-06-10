<?php
session_start();
$db = include(dirname(__FILE__) . '/../../lib/mypdo.php');
require_once(dirname(__FILE__) . '/../../lib/myproject.lib.php');
require_once(dirname(__FILE__) . '/../../class/lot.class.php');
require_once(dirname(__FILE__) . '/../../class/materiel.class.php');

if (!isUserLoggedIn()) {
    header('location: index.php');
    exit(1);
}

if (isset($_POST["cancel"])) {
    header("Location: index.php?element=lots");
    exit(1);
}

if (isset($_POST["submit"])) {
    $nom_lot = filter_input(INPUT_POST, "nom_lot", FILTER_UNSAFE_RAW);
    $nom_lot = sanitize($nom_lot);

    $ids_materiels = $_POST['ids_materiels'] ?? [];
    if (!is_array($ids_materiels)) {
        $ids_materiels = [$ids_materiels];
    }

    $ids_materiels = array_values(array_unique(array_filter(array_map('intval', $ids_materiels), function ($id_materiel) {
        return $id_materiel > 0;
    })));

    if (empty($ids_materiels)) {
        $_SESSION['mesgs']['errors'][] = "Veuillez sélectionner au moins un matériel.";
        header("Location: " . $_SERVER['PHP_SELF'] . "?element=lots&action=add");
        exit(1);
    }

    $data = [
        'nom_lot' => $nom_lot
    ];

    $lot = new Lot($db, $data);
    $lot->create($ids_materiels);

    header("Location: " . $_SERVER['PHP_SELF'] . "?element=lots");
    exit(1);
}

$materiels = Materiel::fetchAll($db);

usort($materiels, function ($a, $b) {
    $dispoA = $a->disponible ? 1 : 0;
    $dispoB = $b->disponible ? 1 : 0;

    if ($dispoA !== $dispoB) {
        return $dispoB <=> $dispoA;
    }

    return $a->id_materiel <=> $b->id_materiel;
});
