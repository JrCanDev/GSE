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

            $materielsLot = $e->fetchMateriels();
            $nomMateriel = '';
            if ($materielsLot && isset($materielsLot[0]['nom_materiel'])) {
                $nomMateriel = $materielsLot[0]['nom_materiel'];
            } elseif (!empty($e->materiels_resume)) {
                $nomMateriel = $e->materiels_resume;
            }

            // on construit un objet léger pour la vue contenant les infos de l'emprunteur et le nom du matériel
            $obj = new stdClass();
            $obj->nom_emprunteur = $e->nom_emprunteur;
            $obj->prenom_emprunteur = $e->prenom_emprunteur;
            $obj->date_prevue_restitution = $e->date_prevue_restitution;
            $obj->nom_materiel = $nomMateriel;

            $overdues[] = $obj;
        }
    }
}

usort($overdues, function ($a, $b) {
    // on trie par date prévue de restitution
    $compDate = $a->date_prevue_restitution <=> $b->date_prevue_restitution;
    if ($compDate !== 0) {
        return $compDate;
    }

    // on trie par ordre alphabétique par NOM
    $compNom = strcasecmp($a->nom_emprunteur, $b->nom_emprunteur);
    if ($compNom !== 0) {
        return $compNom;
    }

    // on trie par ordre alphabétique par PRÉNOM
    return strcasecmp($a->prenom_emprunteur, $b->prenom_emprunteur);
});