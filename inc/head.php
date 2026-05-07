<?php
// if (isset($_POST['cancel']))
//     header("location:index.php");

if (!isset($authorized)) {
    $authorized = false;
}

$basePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
$assetBase = ($basePath === '' || $basePath === '.') ? '' : $basePath;
?>
<!doctype html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <title>GSP - Gestion du système d'emprunt</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css" integrity="sha512-MV7K8+y+gLIBoVD59lQIYicR65iaqukzvf/nwasF0nqhPay5w/9lJmVM2hMDcnK1OnMGCdVK+iQrJ7lzPJQd1w==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="<?php echo $assetBase; ?>/css/styles.css" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <script src="<?php echo $assetBase; ?>/js/scripts.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>
    <div class="fullHead">
        <div class="menutop">
            <?php
            if ($authorized)
                include dirname(__FILE__) . '/top.php';
            ?>
        </div>
    </div>
    <div class="maincontent w3-display-container w3-center">
