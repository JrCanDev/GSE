<h3><b>Fiche Matériel</b></h3>

<div class="col-2 w3-container">
    <form action="?element=materiels&action=card" method="post" class="w3-container w3-card-4 w3-padding">

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

        <!-- Localisation, Étiquette ULCO et État -->
        <div class="w3-row-padding w3-margin-top">
            <div class="w3-third">
                <label><b>Localisation<span style="color: red;">*</span></b></label>
                <input placeholder="Armoire S126" class="w3-input w3-border w3-round w3-center"
                    type="text" name="localisation" required value="<?= sanitize($materiel->localisation) ?>">
            </div>
            <div class="w3-third">
                <label><b>Étiquette ULCO</b></label>
                <input placeholder="10xxx" class="w3-input w3-border w3-round w3-center"
                    type="text" name="etiquette_ulco" required value="<?= sanitize($materiel->etiquette_ulco) ?>">
            </div>
            <div class="w3-third">
                <label><b>État<span style="color: red;">*</span></b></label>
                <select class="w3-select w3-border w3-round w3-center" name="etat" required>
                    <?php foreach (Materiel::$etats as $etat): ?>
                        <option value="<?= htmlspecialchars($etat) ?>" <?= $materiel->etat === $etat ? 'selected' : '' ?>>
                            <?= htmlspecialchars($etat) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- Remarque -->
        <div class="w3-margin-top">
            <label><b>Remarque</b></label>
            <textarea class="w3-input w3-border w3-round w3-center" name="remarque"
                placeholder="C'est certainement une superbe remarque" rows="1"><?= sanitize($materiel->remarque) ?></textarea>
        </div>

        <!-- Descriptif -->
        <div class="w3-margin-top">
            <label><b>Descriptif du matériel</b></label>
            <textarea class="w3-input w3-border w3-round w3-center" name="descriptif"
                placeholder="Processeur : Intel Core i5-9500F" rows="3"><?= sanitize($materiel->descriptif) ?></textarea>
        </div>

        <h3 style="padding-top: 10px;"><b>Historique</b></h3>

        <div class="w3-margin-top">
            <table class="w3-table w3-striped w3-bordered w3-small">
                <thead>
                    <tr class="w3-light-gray">
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

                                <td style="<?= $is_late ? 'color: red; font-weight: bold;' : '' ?>">
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
                <input class="w3-button w3-teal w3-round" type="submit"
                    value="Mettre à jour le matériel" name="update">
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