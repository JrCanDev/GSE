<?php
$db = include(dirname(__FILE__) . '/../../lib/mypdo.php');
require_once(dirname(__FILE__) . '/../../class/materiel.class.php');

$isAdmin = isUserLoggedIn();
$materiels = Materiel::fetchAll($db);