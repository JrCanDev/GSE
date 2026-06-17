<a href="index.php?element=materiels&action=add" class="w3-margin w3-button w3-border w3-round">
    <b>Ajouter un nouveau matériel</b>
</a>

<div class="w3-container w3-margin-bottom w3-margin-top">
    <input class="w3-input w3-border w3-round-xxlarge w3-center"
        type="search"
        id="searchBarMateriel"
        onkeyup="filtrerListeMateriels()"
        placeholder="Rechercher par nom, par identifiant, par état... (plusieurs filtres: virgule ou point-virgule)">
</div>

<?php $totalMateriels = $materiels ? count($materiels) : 0; ?>
<h2 id="compteurMateriels"><?= $totalMateriels ?> / <?= $totalMateriels ?> matériel(s) trouvé(s)</h2>

<table class="w3-table w3-striped w3-bordered w3-small w3-border">
    <thead>
        <tr class="w3-blue">
            <th style="width: 60px;">Photo</th>
            <th>Nom <span class="sort-arrow"></span></th>
            <th>Modèle <span class="sort-arrow"></span></th>
            <th>Année <span class="sort-arrow"></span></th>
            <th>Identifiant <span class="sort-arrow"></span></th>
            <th>Localisation <span class="sort-arrow"></span></th>
            <th>Emprunt <span class="sort-arrow"></span></th>
            <th>État <span class="sort-arrow"></span></th>
            <th>Remarque <span class="sort-arrow"></span></th>
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
                } elseif ($materiel->etat === 'Réservé') {
                    $etat_class = 'w3-text-blue';
                }
                $date_retour_prevue = $materiel->date_retour_prevue ? formatDisplayDate(sanitize($materiel->date_retour_prevue)) : '';
                if ($materiel->disponible) {
                    $emprunt_label = 'Non emprunté';
                } elseif ($date_retour_prevue !== '') {
                    $emprunt_label = 'Emprunté jusqu’au ' . $date_retour_prevue;
                } elseif ($materiel->etat === 'Réservé') {
                    $emprunt_label = 'Réservé';
                } else {
                    $emprunt_label = 'Emprunté';
                }
                $emprunt_class = $materiel->disponible ? 'w3-text-green' : 'w3-text-red';
                ?>

                <tr class="item-materiel">
                    <td>
                        <?php if (!empty($materiel->image_data)): ?>
                            <?php $imgSrc = 'data:' . sanitize($materiel->image_type) . ';base64,' . base64_encode($materiel->image_data); ?>
                            <img src="<?= $imgSrc ?>"
                                alt="<?= "Preview du " . sanitize($materiel->nom) . " (" . sanitize($materiel->etiquette_ulco) . ")" ?>"
                                class="w3-round w3-border img-preview-thumb"
                                style="width: 40px; height: 40px; object-fit: cover; cursor: pointer;"
                                onclick="ouvrirPreview('<?= $imgSrc ?>', '<?= sanitize($materiel->nom) ?>')">
                        <?php endif; ?>
                    </td>
                    <td><?= sanitize($materiel->nom) ?></td>
                    <td><?= sanitize($materiel->modele) ?></td>
                    <td><?= sanitize($materiel->annee) ?></td>
                    <td><?= sanitize($materiel->etiquette_ulco) ?></td>
                    <td><?= sanitize($materiel->localisation) ?></td>
                    <td class="<?= $emprunt_class ?>"><b><?= $emprunt_label ?></b></td>
                    <td class="<?= $etat_class ?>"><?= sanitize($materiel->etat) ?></td>
                    <td><?= desanitize($materiel->remarque) ?></td>
                    <td>
                        <form action="?element=materiels&action=card" method="post">
                            <input type="hidden" name="id_materiel" value="<?= $materiel->id_materiel ?>">
                            <input type="hidden" name="old_page" value="materiels">
                            <input type="submit" name="submit" class="w3-button w3-small w3-border w3-round" value="✏️">
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="9">Aucun matériel trouvé.</td>
            </tr>
        <?php endif ?>
    </tbody>
</table>

<script>
    function filtrerListeMateriels() {
        let input = document.getElementById('searchBarMateriel').value.toLowerCase();
        let rows = document.getElementsByClassName('item-materiel');
        let compteurMateriels = document.getElementById('compteurMateriels');
        let actionColumns = 1;

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

<!-- Modal de prévisualisation d'image -->
<div id="modalPreviewImage" class="w3-modal" onclick="if(event.target===this)fermerPreview()">
    <div class="w3-modal-content w3-animate-zoom" style="max-width: 600px;">
        <div class="w3-container">
            <span onclick="fermerPreview()" class="w3-button w3-display-topright w3-large">&times;</span>
            <h3 id="modalPreviewTitre" class="w3-margin-top"></h3>
        </div>
        <div class="w3-container w3-center w3-padding">
            <img id="modalPreviewImg" src="" alt="Preview" style="max-width: 100%; max-height: 70vh; object-fit: contain; border-radius: 8px;">
        </div>
    </div>
</div>

<script>
    function ouvrirPreview(src, nom) {
        document.getElementById('modalPreviewImg').src = src;
        document.getElementById('modalPreviewTitre').textContent = nom;
        document.getElementById('modalPreviewImage').style.display = 'block';
    }

    function fermerPreview() {
        document.getElementById('modalPreviewImage').style.display = 'none';
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') fermerPreview();
    });
</script>