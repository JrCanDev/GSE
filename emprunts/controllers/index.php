<?php
require_once(dirname(__FILE__) . '/../../lib/mypdo.php');

$emprunt_materiels = array(
    [
        'id' => 1,
        'nom' => 'A',
        'prenom' => 'B',
        'annee' => 'BUT 3 FI',
        'date_emprunt' => '2026-04-28',
        'caution' => 'Déposée',
        'materiel' => 'PC Port. DELL',
        'date_prevue_restitution' => '2026-04-22',
        'date_reelle_restitution' => null,
        'etat' => 'OK',
        'remarque' => null
    ],
    [
        'id' => 2,
        'nom' => 'C',
        'prenom' => 'D',
        'annee' => 'BUT 1 FI',
        'date_emprunt' => '2025-09-04',
        'caution' => 'En attente',
        'materiel' => 'PC Port. DELL',
        'date_prevue_restitution' => '2026-06-19',
        'date_reelle_restitution' => '2025-10-21',
        'etat' => 'Endommagé',
        'remarque' => 'Ordinateur cassé'
    ],
    [
        'id' => 3,
        'nom' => 'E',
        'prenom' => 'F',
        'annee' => 'Autre',
        'date_emprunt' => '2026-02-04',
        'caution' => 'Non demandée',
        'materiel' => 'PC Port. DELL',
        'date_prevue_restitution' => '2026-03-04',
        'date_reelle_restitution' => '2026-03-04',
        'etat' => 'OK',
        'remarque' => null
    ]
);

function formatDisplayDate(?string $date): string
{
    if (empty($date)) {
        return 'En attente';
    }

    $dateTime = DateTime::createFromFormat('Y-m-d', $date);
    if ($dateTime === false) {
        return $date;
    }

    return $dateTime->format('d/m/Y');
}