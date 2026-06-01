<?php
$db = include(dirname(__FILE__) . '/../../lib/mypdo.php');
require_once(dirname(__FILE__) . '/../../lib/myproject.lib.php');
require_once(dirname(__FILE__) . '/../../class/lot.class.php');

$lots = Lot::fetchAll($db);

if (isset($_POST["delete"]) && isset($_POST["id"]) && !empty($_POST["id"])) {
    $id_lot = (int) filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);

    $lot = new Lot($db);
    $lot->fetch($id_lot);

    $lot->delete();
    header("Location: index.php?element=lots");
    exit(1);
}