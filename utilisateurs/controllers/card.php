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

$id_user = GETPOST('id_user');
if (empty($id_user)) {
    $_SESSION['mesgs']['errors'][] = "Identifiant de l'utilisateur non spécifié.";
    header("Location: index.php?element=utilisateurs");
    exit(1);
}

$id_user = (int)$id_user;
$user = new Utilisateur($db);
$user->fetch($id_user);

if ($user->id === -1) {
    $_SESSION['mesgs']['errors'][] = "Utilisateur introuvable.";
    header("Location: index.php?element=utilisateurs");
    exit(1);
}

$isSelf = ($user->id === (int)($_SESSION['user']['id'] ?? -1));

if (GETPOST('update')) {
    $new_username = trim((string) GETPOST('username'));
    $new_username = sanitize($new_username);
    $admin = GETPOST('admin') ? true : false;
    $entite_id = (int) GETPOST('entite_id');

    // on est obligé d'avoir un nom d'utilisateur
    if (empty($new_username)) {
        $_SESSION['mesgs']['errors'][] = "Le nom d'utilisateur ne peut pas être vide.";
        header("Location: index.php?element=utilisateurs&action=card&id_user=" . $user->id);
        exit(1);
    }

    // on vérifie si le nouveau username est déjà utilisé par un AUTRE utilisateur
    if (strtolower($new_username) !== strtolower($user->username)) {
        $existing = myAuthClass::getUserByUsername($new_username);
        if ($existing) {
            $_SESSION['mesgs']['errors'][] = "Le nom d'utilisateur '$new_username' est déjà utilisé par un autre compte.";
            header("Location: index.php?element=utilisateurs&action=card&id_user=" . $user->id);
            exit(1);
        }
    }

    // on ne peut pas enlever ses propres droits administrateur
    if ($isSelf) $admin = true;

    // si l'utilisateur est admin, il n'est pas rattaché à une entité spécifique, on le met donc dans l'entité "Non défini"
    if ($admin) $entite_id = 0;

    $user->username = $new_username;
    $user->admin = $admin;
    $user->entite_id = $entite_id;
    
    if (GETPOST('reset_password') === '1') {
        $user->password = '';
    }

    $user->update();

    header("Location: index.php?element=utilisateurs");
    exit(1);
}

$entites = Entite::fetchAll($db);