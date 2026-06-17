<a href="index.php?element=lots&action=add" class="w3-margin w3-button w3-border w3-round">
    <b>Ajouter un nouveau lot</b>
</a>

<?php $totalLots = $lots ? count($lots) : 0; ?>

<div class="w3-container w3-margin-bottom w3-margin-top">
    <input class="w3-input w3-border w3-round-xxlarge w3-center"
        type="search"
        id="searchBarLot"
        onkeyup="filtrerLots()"
        placeholder="Rechercher par nom de lot ou matériel... (plusieurs filtres: virgule ou point-virgule)">
</div>

<h2 id="compteurLots"><?= $totalLots ?> / <?= $totalLots ?> lot(s) trouvé(s)</h2>

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
                <tr class="item-lot">
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
                <td colspan="3">Aucun lot trouvé.</td>
            </tr>
        <?php endif ?>
    </tbody>
</table>

<script>
    function filtrerLots() {
        let input = document.getElementById('searchBarLot').value.toLowerCase();
        let rows = document.getElementsByClassName('item-lot');
        let compteurLots = document.getElementById('compteurLots');
        let actionColumns = 1;
        let motsCles = input.split(/[,;]/).map(mot => mot.trim()).filter(Boolean);
        let lotsVisibles = 0;

        for (let i = 0; i < rows.length; i++) {
            let cellules = Array.from(rows[i].children);
            let cellulesUtiles = actionColumns > 0 ? cellules.slice(0, -actionColumns) : cellules;

            let texteUtileLigne = cellulesUtiles
                .map(td => td.textContent || td.innerText)
                .join(' ')
                .toLowerCase();

            if (motsCles.length === 0) {
                rows[i].style.display = '';
                lotsVisibles++;
                continue;
            }

            let correspondATousLesMots = motsCles.every(mot => texteUtileLigne.indexOf(mot) > -1);

            if (correspondATousLesMots) {
                rows[i].style.display = '';
                lotsVisibles++;
            } else {
                rows[i].style.display = 'none';
            }
        }

        if (compteurLots) {
            compteurLots.textContent = lotsVisibles + ' / <?= $totalLots ?> lot(s) trouvé(s)';
        }
    }

    filtrerLots();
</script>