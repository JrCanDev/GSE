<h3><b>Fiche Matériel</b></h3>

<div class="col-2 w3-container">
    <form action="?element=materiels&action=card" method="post" enctype="multipart/form-data" class="w3-container w3-card-4 w3-padding">

        <input type="hidden" name="id_materiel" value="<?= sanitize($materiel->id_materiel) ?>">
        <input type="hidden" name="old_page" value="<?= sanitize($old_page) ?>">

        <!-- Nom, Modèle et Année -->
        <div class="w3-row-padding">
            <div class="w3-third">
                <label><b>Nom<span style="color: red;">*</span></b></label>
                <input placeholder="PC Port. DELL" class="w3-input w3-border w3-round w3-center"
                    type="text" name="nom" required value="<?= sanitize($materiel->nom) ?>">
            </div>
            <div class="w3-third">
                <label><b>Modèle</b></label>
                <input placeholder="DELL 16" class="w3-input w3-border w3-round w3-center"
                    type="text" name="modele" value="<?= sanitize($materiel->modele) ?>">
            </div>
            <div class="w3-third">
                <label><b>Année</b></label>
                <input placeholder="2025" class="w3-input w3-border w3-round w3-center"
                    type="number" name="annee" id="annee" min="1900" value="<?= sanitize($materiel->annee) ?>">
            </div>
        </div>

        <!-- Localisation, identifiant et État -->
        <div class="w3-row-padding w3-margin-top">
            <div class="w3-third">
                <label><b>Localisation<span style="color: red;">*</span></b></label>
                <input placeholder="Armoire S126" class="w3-input w3-border w3-round w3-center"
                    type="text" name="localisation" required value="<?= sanitize($materiel->localisation) ?>">
            </div>
            <div class="w3-third">
                <label><b>Identifiant</b></label>
                <input placeholder="10xxx" class="w3-input w3-border w3-round w3-center"
                    type="text" name="etiquette_ulco" value="<?= sanitize($materiel->etiquette_ulco) ?>">
            </div>
            <div class="w3-third">
                <label><b>État<span style="color: red;">*</span></b></label>
                <select id="etat" onchange="changerCouleurSelect(this)" class="w3-select w3-border w3-round w3-center" name="etat"
                    onchange="changerCouleurSelect(this)" required>
                    <?php foreach (Materiel::$etats as $etat):
                        $classEtat = '';
                        if ($etat === 'OK') $classEtat = 'etat-ok';
                        elseif ($etat === 'Réservé') $classEtat = 'etat-reserve';
                        elseif ($etat === 'En réparation') $classEtat = 'etat-reparation';
                        elseif ($etat === 'Endommagé') $classEtat = 'etat-endommage';
                        elseif ($etat === 'Disparu') $classEtat = 'etat-disparu';
                    ?>
                        <option <?= $materiel->etat === $etat ? 'selected' : '' ?>
                            class="<?= $classEtat ?>" value="<?= sanitize($etat) ?>"><?= sanitize($etat) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- Remarque -->
        <div class="w3-margin-top">
            <label><b>Remarque</b></label>
            <textarea class="w3-input w3-border w3-round w3-center" name="remarque"
                placeholder="C'est certainement une superbe remarque" rows="1"><?= desanitize($materiel->remarque) ?></textarea>
        </div>

        <!-- Descriptif -->
        <div class="w3-margin-top">
            <label><b>Descriptif du matériel</b></label>
            <textarea class="w3-input w3-border w3-round w3-center" name="descriptif"
                placeholder="Processeur : Intel Core i5-9500F" rows="3"><?= desanitize($materiel->descriptif) ?></textarea>
        </div>

        <!-- Image -->
        <div class="w3-margin-top">
            <label style="display:block;"><b>Photo</b></label>
            <input class="w3-center w3-input" type="file" name="image_materiel" id="imageInput"
                accept="image/jpeg, image/png, image/webp" onchange="previewImage()">

            <input type="hidden" name="image_status" id="imageStatus" value="keep">

            <?php
            $hasImage = !empty($materiel->image_data);
            $imgSrc = "";
            if ($hasImage) {
                $imageData = is_resource($materiel->image_data) ? stream_get_contents($materiel->image_data) : $materiel->image_data;
                $imgSrc = "data:" . sanitize($materiel->image_type) . ";base64," . base64_encode($imageData);
            }
            ?>

            <div id="previewContainer" class="w3-center w3-margin-top" style="display: <?= $hasImage ? 'block' : 'none' ?>;">
                <div style="position: relative; display: inline-block;">
                    <img src="<?= $imgSrc ?>" id="imagePreview" class="w3-round w3-border" style="max-width: 250px; height: auto;">

                    <button type="button" class="w3-button w3-red w3-round w3-small" style="margin-top: 8px; display: block; width: 100%;" onclick="removeImage()">
                        Enlever la photo
                    </button>
                </div>
            </div>
        </div>

        <!-- Historique -->
        <h3 style="padding-top: 10px;"><b>Historique</b></h3>

        <div class="w3-margin-top">
            <table class="w3-table w3-striped w3-bordered w3-small">
                <thead>
                    <tr class="w3-blue">
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>Année</th>
                        <th>Date d'emprunt</th>
                        <th>Date réelle de restitution</th>
                        <th>Remarque</th>
                        <th>État</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($emprunts): ?>
                        <?php foreach ($emprunts as $emprunt): ?>
                            <tr>
                                <td><?= sanitize($emprunt->nom_emprunteur) ?></td>
                                <td><?= sanitize($emprunt->prenom_emprunteur) ?></td>
                                <td><?= sanitize($emprunt->nom_groupe) ?></td>
                                <td><?= formatDisplayDate(sanitize($emprunt->date_emprunt)) ?></td>

                                <?php
                                $date_prevue = new DateTime($emprunt->date_prevue_restitution);
                                $date_reelle = $emprunt->date_reelle_restitution ? new DateTime($emprunt->date_reelle_restitution) : null;
                                $is_late = $date_reelle && $date_reelle > $date_prevue;

                                $etat_class = '';
                                if ($emprunt->etat_restitution === 'OK') {
                                    $etat_class = 'w3-text-green';
                                } elseif ($emprunt->etat_restitution === 'Endommagé') {
                                    $etat_class = 'w3-text-red';
                                } elseif ($emprunt->etat_restitution === 'En réparation') {
                                    $etat_class = 'w3-text-orange';
                                }
                                ?>

                                <td class="<?= $is_late ? 'w3-text-red' : '' ?>">
                                    <?= formatDisplayDate(sanitize($emprunt->date_reelle_restitution)) ?>
                                </td>
                                <td><?= sanitize($emprunt->remarque_restitution) ?></td>
                                <td class="<?= $etat_class ?>">
                                    <?= sanitize($emprunt->etat_restitution) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="w3-center">Aucun emprunt enregistré pour ce matériel.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="w3-row-padding w3-margin-top">
            <!-- Bouton Mettre à jour -->
            <div class="w3-half w3-right">
                <input class="w3-button w3-blue w3-round" type="submit"
                    value="Mettre à jour le matériel" name="update">
            </div>

            <!-- Bouton Annuler -->
            <div class="w3-half">
                <input class="w3-button w3-red w3-round" type="submit"
                    value="Annuler" name="cancel" formnovalidate>
            </div>
        </div>

        <script>
            document.querySelector("input[name='nom']").focus();

            function previewImage() {
                const fileInput = document.getElementById('imageInput');
                const container = document.getElementById('previewContainer');
                const preview = document.getElementById('imagePreview');
                const statusInput = document.getElementById('imageStatus');

                if (fileInput.files && fileInput.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        preview.src = e.target.result;
                        container.style.display = 'block';
                        statusInput.value = "update";
                    }
                    reader.readAsDataURL(fileInput.files[0]);
                }
            }

            function removeImage() {
                const fileInput = document.getElementById('imageInput');
                const container = document.getElementById('previewContainer');
                const preview = document.getElementById('imagePreview');
                const statusInput = document.getElementById('imageStatus');

                fileInput.value = "";
                preview.src = "";
                container.style.display = 'none';
                statusInput.value = "delete";
            }

            function changerCouleurSelect(selectElement) {
                const optionSelectionnee = selectElement.options[selectElement.selectedIndex];

                selectElement.classList.remove('etat-ok', 'etat-reserve', 'etat-reparation', 'etat-endommage', 'etat-disparu');

                if (optionSelectionnee.className) {
                    selectElement.classList.add(optionSelectionnee.className);
                }
            }

            const selectEtat = document.querySelector("select[name='etat']");
            if (selectEtat) {
                changerCouleurSelect(selectEtat);
            }

            function filtrerMateriel() {
                let input = document.getElementById('searchBar').value.toLowerCase();
                let items = document.getElementsByClassName('item-materiel');

                for (let i = 0; i < items.length; i++) {
                    // on récupère le texte (nom, id et modèle)
                    let texteComplet = items[i].textContent || items[i].innerText;

                    // si le texte contient la recherche, on affiche
                    if (texteComplet.toLowerCase().indexOf(input) > -1) {
                        items[i].style.display = "";
                    } else {
                        items[i].style.display = "none";
                    }
                }
            }

            function toggleSelect(element, inputId, estDisponible) {
                const input = document.getElementById(inputId);
                const idMateriel = inputId.replace('input_', ''); // on récupère l'id du matériel

                if (estDisponible == "0" || estDisponible == "") {
                    return;
                }

                // changer l'apparence
                element.classList.toggle('selection-materiel-selected');

                if (input.value === "0") {
                    input.value = idMateriel;
                } else {
                    input.value = "-1";
                }
            }
        </script>
    </form>
</div>