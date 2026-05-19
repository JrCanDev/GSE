<?php
session_start();
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

$uname = '';
if (isset($_GET['uname'])) {
    $uname = htmlspecialchars($_GET['uname']);
}
?>
<!doctype html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <title>GSE - Première connexion</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <link rel="stylesheet" href="css/styles.css">
</head>

<body>
    <div class="maincontent w3-display-container w3-center">
        <div class="dtitle w3-container main-background-color">
            <h1><b>Première connexion - Définir le mot de passe</b></h1>
        </div>

        <div class="w3-center">
            <?php
            if (!empty($_SESSION['mesgs']['errors'])) {
                foreach ($_SESSION['mesgs']['errors'] as $e) {
                    echo '<div class="w3-panel w3-red">' . htmlspecialchars($e) . '</div>';
                }
                unset($_SESSION['mesgs']['errors']);
            }
            if (!empty($_SESSION['mesgs']['info'])) {
                foreach ($_SESSION['mesgs']['info'] as $e) {
                    echo '<div class="w3-panel w3-blue">' . htmlspecialchars($e) . '</div>';
                }
                unset($_SESSION['mesgs']['info']);
            }
            ?>

            <form action="check_register.php" method="post" style="display: inline-block;">
                <div class="w3-card w3-padding w3-margin w3-row w3-auto">
                    <div class="w3-row-padding">
                        <div class="w3-container w3-margin-top w3-margin-bottom w3-half">
                            <label for="uname" style="display: block"><b>Nom d'utilisateur</b></label>
                            <input type="text" id="uname" name="uname" placeholder="nom d'utilisateur" required>
                        </div>
                        <div class="w3-container w3-margin-top w3-margin-bottom w3-half">
                            <label for="psw" style="display: block"><b>Nouveau mot de passe</b></label>
                            <input type="password" id="psw" name="psw" placeholder="mot de passe" required>
                        </div>
                        <div class="w3-container w3-margin-top w3-margin-bottom w3-half">
                            <label for="psw_confirm" style="display: block"><b>Confirmer mot de passe</b></label>
                            <input type="password" id="psw_confirm" name="psw_confirm" placeholder="confirmation du mdp" required>
                        </div>
                    </div>
                    <div class="w3-margin w3-container">
                        <input type="submit" name="register" value="Définir le mot de passe" class="w3-right w3-button w3-light-green"/>
                        <a href="index.php" class="w3-button w3-red w3-left">Annuler</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <?php include "./inc/footer.php" ?>
</body>

</html>
