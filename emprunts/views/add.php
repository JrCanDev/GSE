<h3><b>Création d'un nouvel emprunt</b></h3>

<div class="col-2 w3-container">
    <form action="<?= $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'] ?>"
        method="post" class="w3-container w3-card-4 w3-padding">

        <div class="w3-row-padding">
            <div class="w3-third">
                <label><b>Nom<span style="color: red;">*</span></b></label>
                <input placeholder="Nom" class="w3-input w3-border w3-round w3-center" type="text" name="nom_emprunteur" required>
            </div>
            <div class="w3-third">
                <label><b>Prénom<span style="color: red;">*</span></b></label>
                <input placeholder="Prénom" class="w3-input w3-border w3-round w3-center" type="text" name="prenom_emprunteur" required>
            </div>
            <div class="w3-third">
                <label><b>Année<span style="color: red;">*</span></b></label>
                <select class="w3-select w3-border w3-round w3-center" name="id_groupe" required>
                    <option value="" disabled selected>Choisir une année</option>
                    <?php
                    foreach ($years as $year): ?>
                        <?php if ($year->est_affiche): ?>
                            <option value="<?= $year->id_groupe ?>" data-date-restitution="<?= sanitize($year->date_restitution) ?>"><?= sanitize($year->nom_groupe) ?></option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="w3-row-padding w3-margin-top">
            <div class="w3-third">
                <label><b>Caution<span style="color: red;">*</span></b></label>
                <select class="w3-select w3-border w3-round w3-center" name="caution" required>
                    <option value="" disabled selected>Choisir une caution</option>
                    <?php foreach (Emprunt::$cautions as $caution): ?>
                        <option value="<?= sanitize($caution) ?>"><?= sanitize($caution) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="w3-third">
                <label><b>Date d’emprunt<span style="color: red;">*</span></b></label>
                <input class="w3-input w3-border w3-round w3-center" type="date" name="date_emprunt" value="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="w3-third">
                <label><b>Date de restitution prévue<span style="color: red;">*</span></b></label>
                <input id="date_prevue_restitution" class="w3-input w3-border w3-round w3-center" type="date" name="date_prevue_restitution" required>
            </div>
        </div>

        <div class="w3-margin-top">
            <label><b>Remarque</b></label>
            <textarea class="w3-input w3-border w3-round w3-center" name="remarque"
                placeholder="C'est certainement une superbe remarque" rows="1"></textarea>
        </div>

        <div class="w3-margin-top w3-border w3-padding w3-round w3-light-grey">
            <label><b><i class="fa fa-boxes"></i> Remplissage rapide par lot :</b></label>
            <select id="selectLotTemplate" class="w3-select w3-border w3-round" onchange="appliquerLotMateriels(this)">
                <option value="" selected>-- Sélectionner un lot prédéfini (Optionnel) --</option>
                <?php foreach ($lotsData as $lot): ?>
                    <option value="<?= $lot['id_lot'] ?>" data-materiels="<?= htmlspecialchars(json_encode($lot['ids_materiels'])) ?>">
                        <?= sanitize($lot['nom_lot']) ?> (<?= count($lot['ids_materiels']) ?> matériels)
                    </option>
                <?php endforeach; ?>
            </select>
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
                            if ($etiquetteUlco !== '') {
                                $infosMateriel[] = $etiquetteUlco;
                            }
                            if ($modele !== '') {
                                $infosMateriel[] = $modele;
                            }
                            if (!$estDisponible) {
                                $classes .= " selection-materiel-indisponible";
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
                                    <?= $estDisponible ? '' : 'disabled' ?>
                                    onchange="toggleMaterielSelection(this)">
                                <span class="txt-materiel"><b><?= sanitize(implode(' | ', $infosMateriel)) ?></b><br><?= sanitize($statutMateriel) ?></span>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="w3-row-padding w3-margin-top">
            <div class="w3-half w3-right">
                <input class="w3-button w3-blue w3-round" type="submit"
                    onclick="verifierSelectionMateriel(event)"
                    value="Créer l'emprunt" name="submit">
            </div>

            <div class="w3-half">
                <input class="w3-button w3-red w3-round" type="submit"
                    value="Annuler" name="cancel" formnovalidate>
            </div>
        </div>

        <script>
            // focus sur champ Nom
            document.querySelector("input[name='nom_emprunteur']").focus();

            // applique date de restitution prévue selon l'année choisie
            function appliquerDateSelonAnnee() {
                const select = document.querySelector("select[name='id_groupe']");
                const inputDate = document.getElementById('date_prevue_restitution');
                if (!select || !inputDate) return;

                const setDateFromOption = (opt) => {
                    if (!opt) {
                        inputDate.value = '';
                        return;
                    }

                    const d = opt.dataset.dateRestitution || '';
                    inputDate.value = d;
                };

                select.addEventListener('change', function() {
                    setDateFromOption(this.options[this.selectedIndex]);
                });

                const initial = select.options[select.selectedIndex];
                if (initial && initial.value !== '') {
                    setDateFromOption(initial);
                }
            }

            appliquerDateSelonAnnee();

            // Coche automatiquement les matériels du lot sélectionné
            function appliquerLotMateriels(selectElement) {
                const optionSelectionnee = selectElement.options[selectElement.selectedIndex];
                if (!optionSelectionnee || !optionSelectionnee.value) return;

                try {
                    // On extrait le tableau d'IDs JSON stocké dans le data attribute de l'option
                    const idsMateriels = JSON.parse(optionSelectionnee.dataset.materiels || '[]');

                    // 1. On décoche tout avant d'appliquer le lot pour partir sur une base propre
                    document.querySelectorAll('.materiel-checkbox').forEach(cb => {
                        if (!cb.disabled && cb.checked) {
                            cb.checked = false;
                            toggleMaterielSelection(cb);
                        }
                    });

                    // 2. On coche chaque matériel présent dans le lot s'il est disponible
                    let nombreMaterielsIndisponibles = 0;
                    idsMateriels.forEach(id => {
                        const checkbox = document.getElementById('materiel-' + id);
                        if (checkbox) {
                            if (!checkbox.disabled) {
                                checkbox.checked = true;
                                toggleMaterielSelection(checkbox);
                            } else {
                                nombreMaterielsIndisponibles++;
                            }
                        }
                    });

                    // Petit avertissement si un ou plusieurs éléments du lot sont déjà loués ailleurs
                    if (nombreMaterielsIndisponibles > 0) {
                        alert("Attention : " + nombreMaterielsIndisponibles + " matériel(s) de ce lot sont actuellement indisponibles et n'ont pas pu être sélectionnés.");
                    }

                } catch (e) {
                    console.error("Erreur lors du traitement du lot :", e);
                }
            }

            function verifierSelectionMateriel(event) {
                const selection = document.querySelectorAll('.materiel-checkbox:checked');

                if (selection.length === 0) {
                    event.preventDefault();
                    alert("Veuillez sélectionner au moins un matériel avant de créer l'emprunt.");
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

            function toggleMaterielSelection(input) {
                const card = input.closest('.selection-materiel');

                if (!card) return;

                if (input.checked) {
                    card.classList.add('selection-materiel-selected');
                } else {
                    card.classList.remove('selection-materiel-selected');
                }
            }

            filtrerMateriel();
        </script>
    </form>
</div>