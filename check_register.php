<?php
session_start();
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

require_once(dirname(__FILE__) . '/class/myAuthClass.php');

if (isset($_POST['register'])) {
    $uname = $_POST['uname'] ?? '';
    $psw = $_POST['psw'] ?? '';
    $psw_confirm = $_POST['psw_confirm'] ?? '';

    if ($psw !== $psw_confirm) {
        $_SESSION['mesgs']['errors'][] = 'Les mots de passe ne correspondent pas.';
        header('Location: register.php?uname=' . urlencode($uname));
        exit;
    }

    if (strlen($psw) < 6) {
        $_SESSION['mesgs']['errors'][] = 'Le mot de passe doit contenir au moins 6 caractères.';
        header('Location: register.php?uname=' . urlencode($uname));
        exit;
    }

    $userRow = myAuthClass::getUserByUsername($uname);
    if (!$userRow) {
        $_SESSION['mesgs']['errors'][] = 'Utilisateur introuvable.';
        header('Location: index.php');
        exit;
    }

    $ok = myAuthClass::register($uname, $psw);
    if ($ok) {
        $_SESSION['mesgs']['confirm'][] = 'Mot de passe défini. Vous pouvez maintenant vous connecter.';
        header('Location: index.php');
        exit;
    } else {
        $_SESSION['mesgs']['errors'][] = 'Erreur lors de l\'enregistrement du mot de passe.';
        header('Location: register.php?uname=' . urlencode($uname));
        exit;
    }
}

header('Location: index.php');
exit;
