<?php
$db = include(dirname(__FILE__) . '/../../lib/mypdo.php');
require_once(dirname(__FILE__) . '/../../class/emprunt.class.php');
require_once(dirname(__FILE__) . '/../../class/materiel.class.php');

$isAdmin = isUserLoggedIn();
$emprunts = Emprunt::fetchAll($db);

usort($emprunts, function($a, $b) {
    // trier d'abord par prêts NON rendus en premier (si un seul matériel)
    // ou si tous les matériels du lot ont été rendus
    $aReturned = !empty($a->date_reelle_restitution) || ((int)$a->nombre_materiels_rendus >= (int)$a->nombre_materiels && (int)$a->nombre_materiels > 0);
    $bReturned = !empty($b->date_reelle_restitution) || ((int)$b->nombre_materiels_rendus >= (int)$b->nombre_materiels && (int)$b->nombre_materiels > 0);

    if ($aReturned && !$bReturned) return 1;
    if (!$aReturned && $bReturned) return -1;

    // trier par nom du groupe
    $cmp = strcmp($a->nom_groupe, $b->nom_groupe);
    if ($cmp !== 0) return $cmp;

    // enfin par nom de l'emprunteur
    return strcmp($a->nom_emprunteur, $b->nom_emprunteur);
});