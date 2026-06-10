<?php
session_start();
$db = include(dirname(__FILE__) . '/../../lib/mypdo.php');
require_once(dirname(__FILE__) . '/../../lib/myproject.lib.php');
require_once(dirname(__FILE__) . '/../../class/utilisateur.class.php');

if (!isUserAdmin()) {
    header('location: index.php');
    exit(1);
}

$utilisateurs = Utilisateur::fetchAll($db);

if (GETPOST('delete') && GETPOST('id')) {
    $id_user = (int) GETPOST('id');
    $current_user_id = (int) ($_SESSION['user']['id'] ?? -1);

    if ($id_user === $current_user_id) {
        $_SESSION['mesgs']['errors'][] = "Vous ne pouvez pas supprimer votre propre compte.";
    } else {
        $user = new Utilisateur($db);
        $user->fetch($id_user);
        
        if ($user->id !== -1) {
            $user->delete();
        } else {
            $_SESSION['mesgs']['errors'][] = "Utilisateur introuvable.";
        }
    }

    header("Location: index.php?element=utilisateurs");
    exit(1);
}
