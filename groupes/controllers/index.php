<?php
$db = include(dirname(__FILE__) . '/../../lib/mypdo.php');
require_once(dirname(__FILE__) . '/../../class/groupe.class.php');

$groupes = Groupe::fetchAll($db);