<?php
session_start();
$db = include(dirname(__FILE__) . '/../../lib/mypdo.php');
require_once(dirname(__FILE__) . '/../../class/groupe.class.php');

if (!isUserLoggedIn()) {
    include dirname(__FILE__) . '/../../login.php';
    exit(1);
}

if (isset($_POST["cancel"])) {
    header("Location: " . $_SERVER['PHP_SELF'] . "?element=groupes");
    exit(1);
}

if (isset($_POST["submit"])) {
    $data = filter_input_array(INPUT_POST, [
        "nom_groupe" => FILTER_UNSAFE_RAW,
        "date_restitution" => FILTER_UNSAFE_RAW,
    ]);

    foreach ($data as $key => $value) {
        if (is_string($value)) $data[$key] = sanitize($value);
    }

    $groupe = new Groupe($db, $data);
    $groupe->create();

    header("Location: " . $_SERVER['PHP_SELF'] . "?element=groupes");
    exit(1);
}