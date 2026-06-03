<?php if ($isAdmin): ?>
    <a href="index.php?element=materiels&action=add" class="w3-margin w3-button w3-border w3-round">
        <b>Ajouter un nouveau matériel</b>
    </a>
<?php endif; ?>

<div class="w3-container w3-margin-bottom w3-margin-top">
    <input class="w3-input w3-border w3-round-xxlarge w3-center"
        type="search"
        id="searchBarMateriel"
        onkeyup="filtrerListeMateriels()"
        placeholder="Rechercher par nom, identifiant, modèle, état... (pour plusieurs filtres, mettre une virgule (,) ou un point-virgule (;) entre les mots-clés)">
</div>

<?php $totalMateriels = $materiels ? count($materiels) : 0; ?>

<table class="w3-table w3-striped w3-bordered w3-small w3-border">
    <thead>
        <tr class="w3-blue">
            <th>Nom <span class="sort-arrow"></span></th>
            <th>Modèle <span class="sort-arrow"></span></th>
            <th>Année <span class="sort-arrow"></span></th>
            <th>Identifiant <span class="sort-arrow"></span></th>
            <?php if ($isAdmin): ?>
                <th>Localisation <span class="sort-arrow"></span></th>
            <?php endif; ?>
            <th>Emprunt <span class="sort-arrow"></span></th>
            <th>État <span class="sort-arrow"></span></th>
            <th>Remarque <span class="sort-arrow"></span></th>
            <?php if ($isAdmin): ?>
                <th>Actions</th>
            <?php endif; ?>
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
                $emprunt_label = $materiel->disponible ? 'Non emprunté' : 'Emprunté';
                $emprunt_class = $materiel->disponible ? 'w3-text-green' : 'w3-text-red';
                ?>

                <tr class="item-materiel">
                    <td><?= sanitize($materiel->nom) ?></td>
                    <td><?= sanitize($materiel->modele) ?></td>
                    <td><?= sanitize($materiel->annee) ?></td>
                    <td><?= sanitize($materiel->etiquette_ulco) ?></td>
                    <?php if ($isAdmin): ?>
                        <td><?= sanitize($materiel->localisation) ?></td>
                    <?php endif; ?>
                    <td class="<?= $emprunt_class ?>"><b><?= $emprunt_label ?></b></td>
                    <td class="<?= $etat_class ?>"><?= sanitize($materiel->etat) ?></td>
                    <td><?= desanitize($materiel->remarque) ?></td>
                    <?php if ($isAdmin): ?>
                        <td>
                            <form action="?element=materiels&action=card" method="post">
                                <input type="hidden" name="id_materiel" value="<?= $materiel->id_materiel ?>">
                                <input type="hidden" name="old_page" value="materiels">
                                <input type="submit" name="submit" class="w3-button w3-small w3-border w3-round" value="✏️">
                            </form>
                        </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="<?= $isAdmin ? 9 : 7 ?>">Aucun matériel trouvé.</td>
            </tr>
        <?php endif ?>
    </tbody>
</table>

<h2 id="compteurMateriels"><?= $totalMateriels ?> / <?= $totalMateriels ?> matériel(s) trouvé(s)</h2>

<script>
    function filtrerListeMateriels() {
        let input = document.getElementById('searchBarMateriel').value.toLowerCase();
        let rows = document.getElementsByClassName('item-materiel');
        let compteurMateriels = document.getElementById('compteurMateriels');
        let actionColumns = <?= $isAdmin ? 1 : 0 ?>;

        let motsCles = input.split(/[,;]/).map(mot => mot.trim()).filter(Boolean);
        let materielsVisibles = 0;

        for (let i = 0; i < rows.length; i++) {
            let cellules = Array.from(rows[i].children);
            let cellulesUtiles = actionColumns > 0 ? cellules.slice(0, -actionColumns) : cellules;

            // on ignore la dernière cellule (la colonne Actions)
            let texteUtileLigne = cellulesUtiles
                .map(td => td.textContent || td.innerText)
                .join(" ")
                .toLowerCase();

            if (motsCles.length === 0) {
                rows[i].style.display = "";
                materielsVisibles++;
                continue;
            }

            // On vérifie si les mots clés sont dans le texte des colonnes utiles
            let correspondAtousLesMots = motsCles.every(mot => texteUtileLigne.indexOf(mot) > -1);

            if (correspondAtousLesMots) {
                rows[i].style.display = "";
                materielsVisibles++;
            } else {
                rows[i].style.display = "none";
            }
        }

        if (compteurMateriels) {
            compteurMateriels.textContent = materielsVisibles + ' / <?= $totalMateriels ?> matériel(s) trouvé(s)';
        }
    }

    filtrerListeMateriels();
</script>