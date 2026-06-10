<?php
session_start();
$db = include(dirname(__FILE__) . '/../../lib/mypdo.php');
require_once(dirname(__FILE__) . '/../../lib/myproject.lib.php');
require_once(dirname(__FILE__) . '/../../class/myAuthClass.php');
require_once(dirname(__FILE__) . '/../../class/utilisateur.class.php');
require_once(dirname(__FILE__) . '/../../class/entite.class.php');

if (!isUserAdmin()) {
    header('location: index.php');
    exit(1);
}

if (GETPOST('cancel')) {
    header("Location: index.php?element=utilisateurs");
    exit(1);
}

if (GETPOST('submit')) {
    $username = trim((string) GETPOST('username'));
    $username = sanitize($username);
    $admin = GETPOST('admin') ? true : false;
    $entite_id = (int) GETPOST('entite_id');

    if (empty($username)) {
        $_SESSION['mesgs']['errors'][] = "Le nom d'utilisateur ne peut pas être vide.";
        header("Location: index.php?element=utilisateurs&action=add");
        exit(1);
    }

    // Vérifier si l'utilisateur existe déjà
    $existing = myAuthClass::getUserByUsername($username);
    if ($existing) {
        $_SESSION['mesgs']['errors'][] = "Le nom d'utilisateur '$username' est déjà utilisé.";
        header("Location: index.php?element=utilisateurs&action=add");
        exit(1);
    }

    $data = [
        'username' => $username,
        'password' => '', // Mot de passe vide par défaut pour la première connexion
        'admin' => $admin,
        'entite_id' => $entite_id
    ];

    $user = new Utilisateur($db, $data);
    $user->create();

    header("Location: index.php?element=utilisateurs");
    exit(1);
}

$entites = Entite::fetchAll($db);