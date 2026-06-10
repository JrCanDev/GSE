<?php if ($isAdmin): ?>
    <a href="index.php?element=groupes&action=add" class="w3-margin w3-button w3-border w3-round">
        <b>Ajouter un nouveau groupe</b>
    </a>
<?php endif; ?>

<h2><b>Groupes actifs</b></h2>
<table class="w3-table w3-striped w3-small w3-bordered w3-border">
    <thead>
        <tr class="w3-blue  ">
            <th>Nom du groupe <span class="sort-arrow"></span></th>
            <th>Date de restitution <span class="sort-arrow"></span></th>
            <?php if ($isAdmin): ?>
                <th>Actions</th>
            <?php endif; ?>
        </tr>
    </thead>
    <tbody>
        <?php if ($groupesActifs): ?>
            <?php foreach ($groupesActifs as $groupe): ?>
                <tr>
                    <td><?= sanitize($groupe->nom_groupe) ?></td>
                    <td><?= formatDisplayDate(sanitize($groupe->date_restitution)) ?></td>
                    <?php if ($isAdmin): ?>
                        <td>
                            <form action="?element=groupes&action=card" method="post">
                                <input type="hidden" name="id_groupe" value="<?= $groupe->id_groupe ?>">
                                <input type="submit" name="edit" class="w3-button w3-small w3-border w3-round" value="✏️">
                            </form>

                            <form action="?element=groupes" method="post">
                                <input type="hidden" name="id" value="<?= $groupe->id_groupe ?>">
                                <input type="submit" name="toggle_state" class="w3-button w3-small w3-border w3-round" value="👁️">
                            </form>
                        </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="11">Aucun groupe trouvé.</td>
            </tr>
        <?php endif ?>
    </tbody>
</table>

<h2 style="padding-top: 30px;"><b>Groupes inactifs</b></h2>
<table class="w3-table w3-striped w3-small w3-bordered w3-border">
    <thead>
        <tr class="w3-blue">
            <th>Nom du groupe</th>
            <th>Date de restitution</th>
            <?php if ($isAdmin): ?>
                <th>Actions</th>
            <?php endif; ?>
        </tr>
    </thead>
    <tbody>
        <?php if ($groupesPasVisible): ?>
            <?php foreach ($groupesPasVisible as $groupe): ?>
                <tr>
                    <td><?= sanitize($groupe->nom_groupe) ?></td>
                    <td><?= formatDisplayDate(sanitize($groupe->date_restitution)) ?></td>
                    <?php if ($isAdmin): ?>
                        <td>
                            <div>
                                <form action="?element=groupes&action=card" method="post">
                                    <input type="hidden" name="id_groupe" value="<?= $groupe->id_groupe ?>">
                                    <input type="submit" name="edit" class="w3-button w3-small w3-border w3-round" value="✏️">
                                </form>

                                <form action="?element=groupes" method="post">
                                    <input type="hidden" name="id" value="<?= $groupe->id_groupe ?>">
                                    <input type="submit" name="toggle_state" class="w3-button w3-small w3-border w3-round" value="👁️">
                                </form>
                            </div>
                        </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="11">Aucun groupe trouvé.</td>
            </tr>
        <?php endif ?>
    </tbody>
</table>