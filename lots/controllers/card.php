<?php
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

if (!isset($_POST['id_lot']) || empty($_POST['id_lot'])) {
	$_SESSION['mesgs']['errors'][] = "ID du lot non spécifié.";
	header("Location: index.php?element=lots");
	exit(-1);
}

$id_lot = (int) $_POST['id_lot'];
$lot = new Lot($db);
$lot->fetch($id_lot);

if ($lot->id_lot === -1) {
	$_SESSION['mesgs']['errors'][] = "Lot introuvable.";
	header("Location: index.php?element=lots");
	exit(-1);
}

if (isset($_POST['update'])) {
	$nom_lot = filter_input(INPUT_POST, 'nom_lot', FILTER_UNSAFE_RAW);
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
	} else {
		$lot->nom_lot = $nom_lot;
		$lot->update($ids_materiels);

		header("Location: index.php?element=lots");
		exit(1);
	}
}

$materiels = Materiel::fetchAll($db);
usort($materiels, function ($a, $b) {
	return $a->id_materiel <=> $b->id_materiel;
});

$materiels_lot = $lot->fetchMaterielsIds();
$ids_materiels_selectionnes = array_map(function ($materiel) {
	return (int) $materiel->id_materiel;
}, $materiels_lot);
