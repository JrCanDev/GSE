<?php
$db = include(dirname(__FILE__) . '/../lib/mypdo.php');
require_once dirname(__FILE__) . '/../lib/myproject.lib.php';
require_once dirname(__FILE__) . '/../class/emprunt.class.php';
require_once dirname(__FILE__) . '/../class/materiel.class.php';

$emprunts = Emprunt::fetchAll($db);
$materiels = Materiel::fetchAll($db);

$now = new DateTime();
$retards = 0;
$en_cours = 0;
$overdues = [];

foreach ($emprunts as $e) {
    $dateReelle = sanitize($e->date_reelle_restitution);
    $etatRestit = sanitize($e->etat_restitution);

    if (empty($dateReelle)) {
        $en_cours++; 
        
        $date_prevue = DateTime::createFromFormat('Y-m-d', $e->date_prevue_restitution);
        
        if ($date_prevue && $date_prevue->format('Y-m-d') < $now->format('Y-m-d')) {
            $retards++;
            // Construire un objet léger pour la vue contenant le nom du matériel
            $materielsLot = $e->fetchMateriels();
            $nomMateriel = '';
            if ($materielsLot && isset($materielsLot[0]['nom_materiel'])) {
                $nomMateriel = $materielsLot[0]['nom_materiel'];
            } elseif (!empty($e->materiels_resume)) {
                $nomMateriel = $e->materiels_resume;
            }

            $obj = new stdClass();
            $obj->nom_emprunteur = $e->nom_emprunteur;
            $obj->prenom_emprunteur = $e->prenom_emprunteur;
            $obj->date_prevue_restitution = $e->date_prevue_restitution;
            $obj->nom_materiel = $nomMateriel;

            $overdues[] = $obj;
        }
    }
}

$totalMateriels = count($materiels);
$dispo = 0;
foreach ($materiels as $m) {
	if (Materiel::estDisponible($db, $m->id_materiel)) $dispo++;
}
$dispo_pct = $totalMateriels > 0 ? round(($dispo / $totalMateriels) * 100) : 0;