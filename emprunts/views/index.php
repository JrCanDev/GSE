<a href="index.php?element=emprunts&action=add" class="w3-margin w3-button w3-border">
    <b>Créer un nouvel emprunt</b>
</a>

<table class="w3-table w3-striped w3-bordered w3-small">
    <thead>
        <tr class="w3-light-gray">
            <th>Nom</th>
            <th>Prénom</th>
            <th>Année</th>
            <th>Date d'emprunt</th>
            <th>Caution</th>
            <th>Matériel</th>
            <th>Date prévue de restitution</th>
            <th>Date réelle de restitution</th>
            <th>État</th>
            <th>Remarque</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($emprunt_materiels): ?>
            <?php foreach ($emprunt_materiels as $emprunt_materiel): ?>
                <?php
                $today = new DateTime();
                $date_prevue = new DateTime($emprunt_materiel['date_prevue_restitution']);
                $date_reelle = $emprunt_materiel['date_reelle_restitution'] ? new DateTime($emprunt_materiel['date_reelle_restitution']) : null;
                $is_late = ($today > $date_prevue && !$date_reelle) || ($date_reelle && $date_reelle > $date_prevue);
                $row_style = $date_reelle ? 'opacity: 0.5;' : '';

                $etat_class = '';
                if ($emprunt_materiel['etat'] === 'OK') {
                    $etat_class = 'w3-text-green';
                } elseif ($emprunt_materiel['etat'] === 'Endommagé') {
                    $etat_class = 'w3-text-red';
                } elseif ($emprunt_materiel['etat'] === 'En réparation') {
                    $etat_class = 'w3-text-orange';
                }
                ?>

                <tr style="<?= $row_style ?>">
                    <td><?= sanitize($emprunt_materiel['nom']) ?></td>
                    <td><?= sanitize($emprunt_materiel['prenom']) ?></td>
                    <td><?= sanitize($emprunt_materiel['annee']) ?></td>
                    <td><?= formatDisplayDate(sanitize($emprunt_materiel['date_emprunt'])) ?></td>
                    <td>
                        <?php if ($emprunt_materiel['caution'] === 'Déposée'): ?>
                            <span style="color: green; font-weight: bold;">✓</span>
                        <?php elseif ($emprunt_materiel['caution'] === 'En attente'): ?>
                            <span style="color: red; font-weight: bold;">✕</span>
                        <?php else: ?>
                            <span style="font-weight: bold;">✕</span>
                        <?php endif; ?>
                    </td>
                    <td><a href="?page=materiels&action=card&id=<?= $emprunt_materiel['materiel_id'] ?>"
                            style="color: #0093d2;">
                            <?= sanitize($emprunt_materiel['materiel']) ?>
                        </a>
                    </td>
                    <td><?= formatDisplayDate(sanitize($emprunt_materiel['date_prevue_restitution'])) ?></td>
                    <td style="<?= $is_late ? 'color: red; font-weight: bold;' : '' ?>">
                        <?= formatDisplayDate(sanitize($emprunt_materiel['date_reelle_restitution'])) ?>
                    </td>
                    <td class="<?= $etat_class ?>" style="font-weight: bold;">
                        <?= sanitize($emprunt_materiel['etat']) ?>
                    </td>
                    <td><?= sanitize($emprunt_materiel['remarque'] ?? '') ?></td>
                    <td>
                        <a href="?page=emprunts&action=modifier&id=<?= $emprunt_materiel['id'] ?>" class="w3-button w3-small w3-border">✏️</a>
                        <?php if (!$emprunt_materiel['date_reelle_restitution']): ?>
                            <a href="?page=emprunts&action=valider&id=<?= $emprunt_materiel['id'] ?>" class="w3-button w3-small w3-border">📥</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Aucun emprunt de matériel trouvé.</p>
        <?php endif ?>
    </tbody>
</table>