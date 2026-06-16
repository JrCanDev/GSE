<h3><b>Ajout d'un nouveau matériel</b></h3>

<div class="col-2 w3-container">
    <form action="<?= $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'] ?>"
        method="post" enctype="multipart/form-data" class="w3-container w3-card-4 w3-padding">

        <!-- Nom, Modèle et Année -->
        <div class="w3-row-padding">
            <div class="w3-third">
                <label><b>Nom<span style="color: red;">*</span></b></label>
                <input placeholder="PC Port. DELL" class="w3-input w3-border w3-round w3-center" type="text" name="nom" required>
            </div>
            <div class="w3-third">
                <label><b>Modèle</b></label>
                <input placeholder="DELL 16" class="w3-input w3-border w3-round w3-center" type="text" name="modele">
            </div>
            <div class="w3-third">
                <label><b>Année</b></label>
                <input placeholder="2025" class="w3-input w3-border w3-round w3-center" type="number" name="annee" id="annee" min="1900">
            </div>
        </div>

        <!-- Localisation, identifiant et État -->
        <div class="w3-row-padding w3-margin-top">
            <div class="w3-third">
                <label><b>Localisation<span style="color: red;">*</span></b></label>
                <input placeholder="Armoire S126" class="w3-input w3-border w3-round w3-center" type="text" name="localisation" required>
            </div>
            <div class="w3-third">
                <label><b>Identifiant<span style="color: red;">*</span></b></label>
                <input placeholder="10xxx" class="w3-input w3-border w3-round w3-center" type="text" name="etiquette_ulco">
            </div>
            <div class="w3-third">
                <label><b>État<span style="color: red;">*</span></b></label>
                <select class="w3-select w3-border w3-round w3-center" name="etat"
                    onchange="changerCouleurSelect(this)" required>
                    <option value="" disabled selected>Choisir un état</option>
                    <?php foreach (Materiel::$etats as $etat):
                        $classEtat = '';
                        if ($etat === 'OK') $classEtat = 'etat-ok';
                        elseif ($etat === 'Réservé') $classEtat = 'etat-reserve';
                        elseif ($etat === 'En réparation') $classEtat = 'etat-reparation';
                        elseif ($etat === 'Endommagé') $classEtat = 'etat-endommage';
                        elseif ($etat === 'Disparu') $classEtat = 'etat-disparu';
                    ?>
                        <option class="<?= $classEtat ?>" value="<?= sanitize($etat) ?>"><?= sanitize($etat) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- Remarque -->
        <div class="w3-margin-top">
            <label><b>Remarque</b></label>
            <textarea class="w3-input w3-border w3-round w3-center" name="remarque"
                placeholder="C'est certainement une superbe remarque" rows="1"></textarea>
        </div>

        <!-- Descriptif -->
        <div class="w3-margin-top">
            <label><b>Descriptif du matériel</b></label>
            <textarea class="w3-input w3-border w3-round w3-center" name="descriptif"
                placeholder="Processeur : Intel Core i5-9500F" rows="3"></textarea>
        </div>

        <!-- Image -->
        <div class="w3-margin-top">
            <label style="display:block;"><b>Photo</b></label>
            <input class="w3-center w3-input" type="file" name="image_materiel" id="imageInput"
                accept="image/jpeg, image/png, image/webp" onchange="previewImage()">

            <div id="previewContainer" class="w3-center w3-margin-top" style="display: none;">
                <div style="position: relative; display: inline-block;">
                    <img src=""
                        id="imagePreview"
                        class="w3-round w3-border"
                        style="max-width: 250px; height: auto;">

                    <button type="button"
                        class="w3-button w3-red w3-round w3-small"
                        style="margin-top: 8px; display: block; width: 100%;"
                        onclick="removeImage()">
                        Enlever la photo
                    </button>
                </div>
            </div>
        </div>

        <div class="w3-row-padding w3-margin-top">
            <!-- Bouton Créer -->
            <div class="w3-half w3-right">
                <input class="w3-button w3-blue w3-round" type="submit"
                    value="Ajouter le matériel" name="submit">
            </div>

            <!-- Bouton Annuler -->
            <div class="w3-half">
                <input class="w3-button w3-red w3-round" type="submit"
                    value="Annuler" name="cancel" formnovalidate>
            </div>
        </div>

        <script>
            const champNom = document.querySelector("input[name='nom']").focus();

            function previewImage() {
                const fileInput = document.getElementById('imageInput');
                const container = document.getElementById('previewContainer');
                const preview = document.getElementById('imagePreview');

                if (fileInput.files && fileInput.files[0]) {
                    const reader = new FileReader();

                    reader.onload = function(e) {
                        preview.src = e.target.result;
                        container.style.display = 'block';
                    }

                    reader.readAsDataURL(fileInput.files[0]);
                } else {
                    removeImage();
                }
            }

            function removeImage() {
                const fileInput = document.getElementById('imageInput');
                const container = document.getElementById('previewContainer');
                const preview = document.getElementById('imagePreview');

                fileInput.value = "";

                preview.src = "";
                container.style.display = 'none';
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