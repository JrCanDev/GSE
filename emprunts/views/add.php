<h3><b>Création d'un nouvel emprunt</b></h3>

<div class="col-2 w3-container">
    <form action="<?= $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'] ?>"
        method="post" class="w3-container w3-card-4 w3-padding">

        <!-- Prénom, Nom et Année BUT -->
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

        <!-- Caution, Date d’emprunt et Date de restitution prévue -->
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

        <!-- Remarque -->
        <div class="w3-margin-top">
            <label><b>Remarque</b></label>
            <textarea class="w3-input w3-border w3-round w3-center" name="remarque"
                placeholder="C'est certainement une superbe remarque" rows="1"></textarea>
        </div>

        <h3 style="padding-top: 10px;"><b>Sélection du matériel<span style="color: red;">*</span></b></h3>

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
                            $classes = "w3-border w3-round-xxlarge w3-center w3-padding-small selection-materiel";
                            $estDisponible = Materiel::estDisponible($db, $m->id_materiel);
                            $etiquetteUlco = trim((string) ($m->etiquette_ulco ?? ''));
                            $modele = trim((string) ($m->modele ?? ''));
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
                                <span class="txt-materiel"><b><?= sanitize(implode(' | ', $infosMateriel)) ?></b></span>
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
                    onclick="verifierSelectionMateriel(event)"
                    value="Créer l'emprunt" name="submit">
            </div>

            <!-- Bouton Annuler -->
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

            function verifierSelectionMateriel(event) {
                const selection = document.querySelectorAll('.materiel-checkbox:checked');
                
                if (selection.length === 0) {
                    event.preventDefault();
                    alert("Veuillez sélectionner au moins un matériel avant de créer l'emprunt.");
                }
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