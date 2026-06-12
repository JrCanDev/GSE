<?php
$db = include(dirname(__FILE__) . '/../../lib/mypdo.php');
require_once(dirname(__FILE__) . '/../../class/materiel.class.php');

$materiels = Materiel::fetchAll($db);