<?php
session_start();
$db = include(dirname(__FILE__) . '/../../lib/mypdo.php');
require_once(dirname(__FILE__) . '/../../lib/myproject.lib.php');
require_once(dirname(__FILE__) . '/../../class/myAuthClass.php');
require_once(dirname(__FILE__) . '/../../class/utilisateur.class.php');

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

    if (empty($new_username)) {
        $_SESSION['mesgs']['errors'][] = "Le nom d'utilisateur ne peut pas être vide.";
        header("Location: index.php?element=utilisateurs&action=card&id_user=" . $user->id);
        exit(1);
    }

    // Vérifier si le nouveau username est déjà utilisé par un AUTRE utilisateur
    if (strtolower($new_username) !== strtolower($user->username)) {
        $existing = myAuthClass::getUserByUsername($new_username);
        if ($existing) {
            $_SESSION['mesgs']['errors'][] = "Le nom d'utilisateur '$new_username' est déjà utilisé par un autre compte.";
            header("Location: index.php?element=utilisateurs&action=card&id_user=" . $user->id);
            exit(1);
        }
    }

    // Sécurité : on ne peut pas enlever ses propres droits administrateur
    if ($isSelf) {
        $admin = true;
    }

    $user->username = $new_username;
    $user->admin = $admin;

    // Si on a coché la réinitialisation du mot de passe
    if (GETPOST('reset_password') === '1') {
        $user->password = '';
    } else {
        // Pour éviter de modifier le mot de passe actuel s'il n'est pas réinitialisé, 
        // on ne définit pas la propriété password ou on la laisse à sa valeur de fetch.
        // Dans notre classe Utilisateur::update(), la modification de password n'est faite 
        // que si isset($this->password) et si elle n'est pas nulle/vide. 
        // Cependant, le constructeur/hydrate l'a chargée. 
        // Donc, si reset_password n'est PAS coché, on unset($user->password) avant l'update !
        unset($user->password);
    }

    $user->update();

    // Mettre à jour la session de l'admin lui-même s'il vient de modifier ses propres infos (comme son username)
    if ($isSelf) {
        $_SESSION['user']['username'] = $user->username;
        $_SESSION['user']['admin'] = $user->admin;
    }

    header("Location: index.php?element=utilisateurs");
    exit(1);
}
