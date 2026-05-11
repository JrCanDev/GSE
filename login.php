<?php
session_start(); // Démarrage de la session
// print_r($_SESSION);
// exit();
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

$db = require(dirname(__FILE__) . '/lib/mypdo.php');
?>
<!doctype html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <title>GSE - Connexion</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css" integrity="sha512-MV7K8+y+gLIBoVD59lQIYicR65iaqukzvf/nwasF0nqhPay5w/9lJmVM2hMDcnK1OnMGCdVK+iQrJ7lzPJQd1w==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="css/styles.css">
    <script src="js/scripts.js"></script>
</head>

<body>
    <div class="maincontent w3-display-container w3-center">
        <div class="dtitle w3-container main-background-color">
            <h1>Authentification</h1>
        </div>

        <div class="w3-center">
            <form action="check_login.php" method="post" style="display: inline-block;">
                <div class="w3-card w3-padding w3-margin w3-row w3-auto">
                    <div class="w3-row-padding">
                        <div class="w3-container w3-margin-top w3-margin-bottom w3-half">
                            <label for="uname" style="display: block"><b>Nom d'utilisateur</b></label>
                            <input type="text" id="uname" placeholder="nom d'utilisateur" name="uname" required>
                        </div>
                        <div class="w3-container w3-margin-top w3-margin-bottom w3-half">
                            <label for="psw" style="display: block"><b>Mot de passe</b></label>
                            <input type="password" id="psw" placeholder="mot de passe" name="psw" required>
                        </div>
                    </div>
                    <div class="w3-margin">
                        <?php if (is_null($db)) {
                        ?>
                            <input type="submit" name="connect" value="Impossible de se connecter" disabled class="w3-light-green w3-button"/>
                        <?php
                        } else {
                        ?>
                            <input type="submit" name="connect" value="Se connecter" class="w3-light-green w3-button"/>
                        <?php
                        }
                        ?>
                    </div>
                </div>
            </form>
        </div>
</div>

<?php include "./inc/footer.php" ?>