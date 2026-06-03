<h3><b>Ajout d'un nouveau lot</b></h3>

<div class="col-2 w3-container">
    <form action="<?= $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'] ?>"
        method="post" class="w3-container w3-card-4 w3-padding">

        <!-- Nom du lot -->
        <div class="w3-row-padding">
            <label><b>Nom du lot<span style="color: red;">*</span></b></label>
            <input placeholder="Lot ..." class="w3-input w3-border w3-round w3-center" type="text" name="nom_lot" required>
        </div>
        <h3 style="padding-top: 10px;"><b>Sélection du matériel<span style="color: red;">*</span></b></h3>

        <input class="w3-input w3-border w3-round-xxlarge w3-center black-border"
            type="search" id="searchBar" onkeyup="filtrerMateriel()"
            placeholder="Rechercher par nom, ID ou modèle...">

        <label class="w3-margin-top w3-left-align" style="display:block;">
            <input type="checkbox" id="afficherMaterielsUtilises" onchange="filtrerMateriel()">
            Afficher les matériels déjà utilisés
        </label>

        <div>
            <div class="scroll-container w3-margin-top">
                <div class="w3-row-padding">
                    <?php foreach ($materiels as $m): ?>
                            <?php
                            $classes = "w3-border w3-round-xxlarge w3-center w3-padding-small selection-materiel";
                            $estDisponible = (bool) $m->disponible;
                            $etiquetteUlco = trim((string) ($m->etiquette_ulco ?? ''));
                            $modele = trim((string) ($m->modele ?? ''));
                            $dateRetourPrevue = $m->date_retour_prevue ? formatDisplayDate(sanitize($m->date_retour_prevue)) : '';
                            $statutMateriel = $estDisponible ? 'Disponible' : ($dateRetourPrevue !== '' ? 'Emprunté jusqu’au ' . $dateRetourPrevue : ($m->etat === 'Réservé' ? 'Réservé' : 'Indisponible'));
                            $infosMateriel = [$m->nom];

                            if (!$estDisponible) {
                                $classes .= " selection-materiel-utilise";
                            }

                            if ($etiquetteUlco !== '') {
                                $infosMateriel[] = $etiquetteUlco;
                            }

                            if ($modele !== '') {
                                $infosMateriel[] = $modele;
                            }
                            ?>
                        <div class="w3-third w3-margin-bottom item-materiel" data-utilise="<?= $estDisponible ? '0' : '1' ?>">
                            <label class="<?= $classes ?>" for="materiel-<?= $m->id_materiel ?>">
                                <input
                                    class="materiel-checkbox"
                                    type="checkbox"
                                    id="materiel-<?= $m->id_materiel ?>"
                                    name="ids_materiels[]"
                                    value="<?= $m->id_materiel ?>"
                                    style="display:none;"
                                    onchange="toggleMaterielSelection(this)">
                                <span class="txt-materiel"><b><?= sanitize(implode(' | ', $infosMateriel)) ?></b><br><?= sanitize($statutMateriel) ?></span>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="w3-row-padding w3-margin-top">
            <!-- Bouton Créer -->
            <div class="w3-half w3-right">
                <input class="w3-button w3-blue w3-round" type="submit"
                    value="Ajouter le lot" name="submit">
            </div>

            <!-- Bouton Annuler -->
            <div class="w3-half">
                <input class="w3-button w3-red w3-round" type="submit"
                    value="Annuler" name="cancel" formnovalidate>
            </div>
        </div>

        <script>
            // focus sur champ Nom
            document.querySelector("input[name='nom_lot']").focus();

            function verifierSelectionMateriel(event) {
                const selection = document.querySelectorAll('.materiel-checkbox:checked');

                if (selection.length === 0) {
                    event.preventDefault();
                    alert("Veuillez sélectionner au moins un matériel avant de créer le lot.");
                }
            }

            function filtrerMateriel() {
                let input = document.getElementById('searchBar').value.toLowerCase();
                let afficherUtilises = document.getElementById('afficherMaterielsUtilises').checked;
                let items = document.getElementsByClassName('item-materiel');

                for (let i = 0; i < items.length; i++) {
                    let texteComplet = items[i].textContent || items[i].innerText;
                    let estUtilise = items[i].dataset.utilise === '1';

                    if ((!estUtilise || afficherUtilises) && texteComplet.toLowerCase().indexOf(input) > -1) {
                        items[i].style.display = "";
                    } else {
                        items[i].style.display = "none";
                    }
                }
            }

            filtrerMateriel();

            function toggleMaterielSelection(input) {
                const card = input.closest('.selection-materiel');

                if (!card) return;

                if (input.checked) {
                    card.classList.add('selection-materiel-selected');
                } else {
                    card.classList.remove('selection-materiel-selected');
                }
            }
        </script>
    </form>
</div>
