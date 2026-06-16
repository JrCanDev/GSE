<?php
$db = include(dirname(__FILE__) . '/../../lib/mypdo.php');
require_once(dirname(__FILE__) . '/../../lib/myproject.lib.php');
require_once(dirname(__FILE__) . '/../../class/materiel.class.php');
require_once(dirname(__FILE__) . '/../../class/emprunt.class.php');

if (!isUserLoggedIn()) {
    header('location: index.php');
    exit(1);
}

$old_page = GETPOST('old_page') ?? 'materiels';

if (isset($_POST["cancel"])) {
    header("Location: index.php?element=" . $old_page);
    exit(1);
}

$id_materiel = GETPOST('id_materiel');
if (empty($id_materiel)) {
    $_SESSION['mesgs']['errors'][] = "ID du matériel non spécifié.";
    header("Location: " . $_SERVER['PHP_SELF'] . "?element=" . $old_page);
    exit(-1);
}
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

    $image_status = $_POST['image_status'] ?? 'keep';

    if ($image_status === 'delete') {
        $materiel->image_data = null;
        $materiel->image_type = null;
    } elseif ($image_status === 'update' || (isset($_FILES['image_materiel']) && $_FILES['image_materiel']['error'] === 0)) {
        $tmp_name = $_FILES['image_materiel']['tmp_name'];

        $check = getimagesize($tmp_name);
        if ($check !== false) {
            $materiel->image_data = file_get_contents($tmp_name);
            $materiel->image_type = $_FILES['image_materiel']['type'];
        } else {
            $_SESSION['mesgs']['errors'][] = "Le fichier téléversé n'est pas une image valide.";
            header("Location: index.php?element=" . $old_page);
            exit(1);
        }
    }

    $materiel->update();

    header("Location: index.php?element=" . $old_page);
    exit;
}
