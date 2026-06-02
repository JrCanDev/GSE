<?php
session_start(); // Démarrage de la session
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
require_once(dirname(__FILE__) . '/../class/myAuthClass.php');

require_once(dirname(__FILE__) . '/myproject.lib.php');

$action = GETPOST('action') ?? 'index';
$element = GETPOST('element') ?? '';
$publicReadOnlyElements = ['', '.', 'accueil', 'materiels', 'lots', 'emprunts', 'groupes', 'statistiques'];
$publicReadOnlyRoute = ($action === 'index' && in_array($element, $publicReadOnlyElements, true));

$authorized = myAuthClass::is_auth($_SESSION);
if (!$authorized && !$publicReadOnlyRoute) {
    include dirname(__FILE__).'/../login.php';
    exit(1);
}
