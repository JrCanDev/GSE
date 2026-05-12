<a href="index.php?element=groupes&action=add" class="w3-margin w3-button w3-border">
    <b>Ajouter un nouveau groupe</b>
</a>

<table class="w3-table w3-striped w3-bordered w3-small">
    <thead>
        <tr class="w3-light-gray">
            <th>Nom du groupe</th>
            <th>Date de restitution</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($groupes): ?>
            <?php foreach ($groupes as $groupe): ?>
                <?php
                $row_style = $groupe->est_affiche ? '' : 'opacity: 0.5;';
                ?>

                <tr>
                    <td style="<?= $row_style ?>"><?= sanitize($groupe->nom_groupe) ?></td>
                    <td style="<?= $row_style ?>"><?= formatDisplayDate(sanitize($groupe->date_restitution)) ?></td>
                    <td>
                        <form action="?element=groupes&action=card" method="post">
                            <input type="hidden" name="id_groupe" value="<?= $groupe->id_groupe ?>">
                            <input type="submit" name="edit" class="w3-button w3-small w3-border w3-round" value="✏️">
                        </form>

                        <form action="?element=groupes" method="post">
                            <input type="hidden" name="id" value="<?= $groupe->id_groupe ?>">
                            <input type="submit" name="toggle_state" class="w3-button w3-small w3-border w3-round" value="👁️">
                        </form>

                        <form action="?element=groupes" method="post"
                            onsubmit="return confirm('Voulez-vous vraiment supprimer ce groupe ?');">
                            <input type="hidden" name="id" value="<?= $groupe->id_groupe ?>">
                            <input type="submit" name="delete" class="w3-button w3-small w3-border w3-round" value="🗑️">
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="11">Aucun groupe trouvé.</td>
            </tr>
        <?php endif ?>
    </tbody>
</table>