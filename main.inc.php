<?php
// Résolution de la route
$action = GETPOST('action') ?? 'index';
$element = GETPOST('element') ?? '.';
$target_c = dirname(__FILE__) . "/$element/controllers/$action.php";
$target_v = dirname(__FILE__) . "/$element/views/$action.php";
$route_exists = (is_file($target_c) && is_file($target_v));

// Phase 1 : contrôleur EN PREMIER, avant tout HTML
// Si le contrôleur fait un header(Location)+exit(), aucun HTML ne sera émis.
if ($route_exists) {
    include($target_c);
}

// Phase 2 : rendu HTML (on n'arrive ici que si aucune redirection n'a eu lieu)
include dirname(__FILE__) . "/inc/head.php";
?>
<div class="body">
    <?php
    if ($route_exists) {
        include($target_v);
    } else {
        include(dirname(__FILE__) . '/inc/notfound.php');
    }
    ?>
</div>
<?php
include dirname(__FILE__) . "/inc/footer.php";