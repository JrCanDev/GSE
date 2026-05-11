<a href="index.php?element=materiels&action=add" class="w3-margin w3-button w3-border">
    <b>Ajouter un nouveau matériel</b>
</a>

<table class="w3-table w3-striped w3-bordered w3-small">
    <thead>
        <tr class="w3-light-gray">
            <th>Nom</th>
            <th>Modèle</th>
            <th>Année</th>
            <th>Étiquette ULCO</th>
            <th>Localisation</th>
            <th>État</th>
            <th>Remarque</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($materiels): ?>
            <?php foreach ($materiels as $materiel): ?>
                <?php
                $etat_class = '';
                if ($materiel->etat === 'OK') {
                    $etat_class = 'w3-text-green';
                } elseif ($materiel->etat === 'Endommagé') {
                    $etat_class = 'w3-text-red';
                } elseif ($materiel->etat === 'En réparation') {
                    $etat_class = 'w3-text-orange';
                }
                ?>

                <tr>
                    <td><?= sanitize($materiel->nom) ?></td>
                    <td><?= sanitize($materiel->modele) ?></td>
                    <td><?= sanitize($materiel->annee) ?></td>
                    <td><?= sanitize($materiel->etiquette_ulco) ?></td>
                    <td><?= sanitize($materiel->localisation) ?></td>
                    <td class="<?= $etat_class ?>"><?= sanitize($materiel->etat) ?></td>
                    <td><?= sanitize($materiel->remarque ?? '') ?></td>
                    <td>
                        <form action="?element=materiels&action=card" method="post">
                            <input type="hidden" name="id_materiel" value="<?= $materiel->id_materiel ?>">
                            <input type="submit" name="submit" class="w3-button w3-small w3-border w3-round" value="✏️">
                        </form>

                        <form action="?element=materiels" method="post"
                            onsubmit="return confirm('Voulez-vous vraiment supprimer ce matériel ?');">
                            <input type="hidden" name="id" value="<?= $materiel->id_materiel ?>">
                            <input type="submit" name="delete" class="w3-button w3-small w3-border w3-round" value="🗑️">
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="8">Aucun matériel trouvé.</td>
            </tr>
        <?php endif ?>
    </tbody>
</table>