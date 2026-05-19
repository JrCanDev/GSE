<?php
session_start();
// Permet d'activer l'affichage des erreurs
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);

require_once(dirname(__FILE__) . '/lib/myproject.lib.php');
require_once(dirname(__FILE__) . '/class/myAuthClass.php');

if (GETPOST('debug') == true) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
}

if (isset($_POST['connect'])) {
    $uname = $_POST['uname'];
    $psw = $_POST['psw'];
    // Si l'utilisateur existe mais n'a pas de mot de passe défini -> première connexion
    $userRow = myAuthClass::getUserByUsername($uname);
    if ($userRow && (is_null($userRow['password']) || $userRow['password'] === '')) {
        $_SESSION['mesgs']['info'][] = 'Première connexion : définissez votre mot de passe.';
        header('Location: register.php?uname=' . urlencode($uname));
        exit;
    }

    $user = myAuthClass::authenticate($uname, $psw);

    if ($user && isset($user["id"]) && $user["id"] > 0) {
        $_SESSION['mesgs']['confirm'][] = 'Connexion réussie ' . $user['username'];
        $_SESSION['login'] = $user['username'];
        $_SESSION['user'] = $user;
    } else {
        $_SESSION['mesgs']['errors'][] = 'Identification impossible';
    }
}

header('Location:index.php');
