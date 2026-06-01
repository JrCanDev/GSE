<a href="index.php?element=lots&action=add" class="w3-margin w3-button w3-border w3-round">
    <b>Ajouter un nouveau lot</b>
</a>

<h2><b>Lots</b></h2>
<table class="w3-table w3-striped w3-small w3-bordered w3-border">
    <thead>
        <tr class="w3-blue  ">
            <th>Nom du lot <span class="sort-arrow"></span></th>
            <th>Matériels <span class="sort-arrow"></span></th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($lots): ?>
            <?php foreach ($lots as $lot): ?>
                <tr>
                    <td><?= sanitize($lot->nom_lot) ?></td>
                    <td>
                        <?php $materiels = $lot->fetchMaterielsIds(); ?>
                        <?php if (!empty($materiels)): ?>
                            <?= implode(
                                ', ',
                                array_map(function ($m) {
                                    return sanitize($m->nom) . ' (#' . $m->id_materiel . ')';
                                }, $materiels)
                            );
                            ?>
                        <?php else: ?>
                            Aucun matériel associé.
                        <?php endif; ?>
                    </td>
                    <td>
                        <form action="?element=lots&action=card" method="post">
                            <input type="hidden" name="id_lot" value="<?= $lot->id_lot ?>">
                            <input type="submit" name="edit" class="w3-button w3-small w3-border w3-round" value="✏️">
                        </form>

                        <form action="?element=lots" method="post"
                            onsubmit="return confirm('Voulez-vous vraiment supprimer ce lot ?');">
                            <input type="hidden" name="id" value="<?= $lot->id_lot ?>">
                            <input type="submit" name="delete" class="w3-button w3-small w3-border w3-round" value="🗑️">
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="11">Aucun lot trouvé.</td>
            </tr>
        <?php endif ?>
    </tbody>
</table>