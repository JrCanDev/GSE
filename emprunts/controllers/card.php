<?php
$db = include(dirname(__FILE__) . '/../../lib/mypdo.php');
require_once(dirname(__FILE__) . '/../../lib/myproject.lib.php');
require_once(dirname(__FILE__) . '/../../class/emprunt.class.php');
require_once(dirname(__FILE__) . '/../../class/materiel.class.php');

if (!isUserLoggedIn()) {
    include dirname(__FILE__) . '/../../login.php';
    exit(1);
}

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
        // si c'est AJAX, on renvoie un code d'erreur HTTP 400
        http_response_code(400);
        echo "ID du matériel non spécifié.";
        exit;
    }

    $etat_restitution = sanitize($_POST['etat_restitution'] ?? '');
    if (!in_array($etat_restitution, Materiel::$etats, true)) {
        $etat_restitution = null;
    }

    $remarque_restitution = html_entity_decode($_POST['remarque_restitution'] ?? '', ENT_QUOTES, 'UTF-8');
    if ($remarque_restitution === '') {
        $remarque_restitution = null;
    }

    try {
        $emprunt->rendreMateriel($id_materiel, $etat_restitution, $remarque_restitution, date('Y-m-d'), true);

        // on prépare du JSON contenant le nouvel état global de l'emprunt
        header('Content-Type: application/json');

        // on va déterminer l'état global pour mettre à jour la vue index.php
        $etat_global = $emprunt->date_reelle_restitution ? $emprunt->etat_restitution : 'En cours';

        echo json_encode([
            'success' => true,
            'nombre_materiels_rendus' => $emprunt->nombre_materiels_rendus,
            'nombre_materiels' => $emprunt->nombre_materiels,
            'date_reelle_restitution' => formatDisplayDate($emprunt->date_reelle_restitution),
            'etat_global' => $etat_global,
            'remarque_restitution' => $emprunt->remarque_restitution
        ]);
        exit;
    } catch (Exception $e) {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode(['error' => $e->getMessage()]);
        exit;
    }
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
