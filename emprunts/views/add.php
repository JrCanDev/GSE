<h3><b>Création d'un nouvel emprunt</b></h3>

<div class="col-2 w3-container">
    <form action="<?= $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'] ?>"
        method="post" class="w3-container w3-card-4 w3-padding">

        <!-- Prénom, Nom et Année BUT -->
        <div class="w3-row-padding">
            <div class="w3-third">
                <label><b>Nom</b></label>
                <input placeholder="Nom" class="w3-input w3-border w3-round w3-center" type="text" name="lastname" required>
            </div>
            <div class="w3-third">
                <label><b>Prénom</b></label>
                <input placeholder="Prénom" class="w3-input w3-border w3-round w3-center" type="text" name="firstname" required>
            </div>
            <div class="w3-third">
                <label><b>Année</b></label>
                <select class="w3-select w3-border w3-round w3-center" name="year" required>
                    <option value="" disabled selected>Choisir une année</option>
                    <?php foreach ($years as $year): ?>
                        <option value="<?= htmlspecialchars($year) ?>"><?= htmlspecialchars($year) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- Caution, Date d’emprunt et Date de restitution prévue -->
        <div class="w3-row-padding w3-margin-top">
            <div class="w3-third">
                <label><b>Caution</b></label>
                <select class="w3-select w3-border w3-round w3-center" name="caution" required>
                    <option value="" disabled selected>Choisir une caution</option>
                    <option value="Déposée">Déposée</option>
                    <option value="En attente">En attente</option>
                    <option value="Non demandée">Non demandée</option>
                </select>
            </div>
            <div class="w3-third">
                <label><b>Date d’emprunt</b></label>
                <input class="w3-input w3-border w3-round w3-center" type="date" name="date_emprunt" value="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="w3-third">
                <label><b>Date de restitution prévue</b></label>
                <input class="w3-input w3-border w3-round w3-center" type="date" name="date_restitution_prevue" required>
            </div>
        </div>

        <!-- Remarque -->
        <div class="w3-margin-top">
            <label><b>Remarque</b></label>
            <textarea class="w3-input w3-border w3-round w3-center" name="remarque"
                placeholder="C'est certainement une superbe remarque" rows="1"></textarea>
        </div>

        <h3 style="padding-top: 10px;"><b>Sélection du matériel</b></h3>

        <input class="w3-input w3-border w3-round-xxlarge w3-center black-border"
            type="search" id="searchBar" onkeyup="filtrerMateriel()"
            placeholder="Rechercher par nom, ID ou modèle...">

        <div>
            <div class="scroll-container w3-margin-top">
                <div class="w3-row-padding">
                    <?php foreach ($materiels as $m): ?>
                        <div class="w3-third w3-margin-bottom item-materiel">
                            <?php
                            // Si le matériel n'est pas disponible, on grise la carte et on empêche la sélection
                            $classes = "w3-border w3-round-xxlarge w3-center w3-padding-small";
                            if (!$m['disponible']) {
                                $classes .= " selection-materiel-indisponible";
                            } else {
                                $classes .= " selection-materiel";
                            }
                            ?>
                            <div onclick="toggleSelect(this, 'input_<?= $m['id'] ?>', '<?= $m['disponible'] ? 1 : 0 ?>')"
                                class="<?= $classes ?>">

                                <span class="txt-nom"><b><?= $m['nom'] ?></b></span> |
                                <span class="txt-id"><b><?= $m['id'] ?></b></span> |
                                <span class="txt-modele"><b><?= $m['modele'] ?></b></span>

                                <input type="hidden" name="materiel_selectionne[]" id="input_<?= $m['id'] ?>" value="0">
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="w3-row-padding w3-margin-top">
            <!-- Bouton Créer -->
            <div class="w3-half w3-right">
                <input class="w3-button w3-teal w3-round" type="submit"
                    value="Créer l'emprunt" name="submit">
            </div>

            <!-- Bouton Annuler -->
            <div class="w3-half">
                <input class="w3-button w3-red w3-round" type="submit"
                    value="Annuler" name="cancel" formnovalidate>
            </div>
        </div>

        <script>
            document.querySelector("input[name='lastname']").focus();

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