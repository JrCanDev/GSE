<a href="index.php?element=emprunts&action=add" class="w3-margin w3-button w3-border w3-round">
    <b>Créer un nouvel emprunt</b>
</a>

<div class="w3-container w3-margin-bottom w3-margin-top">
    <input class="w3-input w3-border w3-round-xxlarge w3-center"
        type="search"
        id="searchBarEmprunt"
        onkeyup="filtrerEmprunts()"
        placeholder="Rechercher par nom, par matériel, par date... (plusieurs filtres: virgule ou point-virgule)">
</div>

<?php $totalEmprunts = $emprunts ? count($emprunts) : 0; ?>

<table class="w3-table w3-striped w3-bordered w3-small w3-border">
    <thead>
        <tr class="w3-blue">
            <th>Nom <span class="sort-arrow"></span></th>
            <th>Prénom <span class="sort-arrow"></span></th>
            <th>Année <span class="sort-arrow"></span></th>
            <th>Date d'emprunt <span class="sort-arrow"></span></th>
            <th>Caution <span class="sort-arrow"></span></th>
            <th>Matériel <span class="sort-arrow"></span></th>
            <th>Date prévue de restitution <span class="sort-arrow"></span></th>
            <th>Date réelle de restitution <span class="sort-arrow"></span></th>
            <th>État <span class="sort-arrow"></span></th>
            <th>Remarque <span class="sort-arrow"></span></th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($emprunts): ?>
            <?php foreach ($emprunts as $emprunt): ?>
                <?php
                $materielsLot = $emprunt->fetchMateriels();
                $today = new DateTime();
                $date_prevue = new DateTime($emprunt->date_prevue_restitution);
                $date_reelle = $emprunt->date_reelle_restitution ? new DateTime($emprunt->date_reelle_restitution) : null;
                $is_late = ($today > $date_prevue && !$date_reelle) || ($date_reelle && $date_reelle > $date_prevue);
                $row_style = $date_reelle ? 'opacity: 0.5;' : '';
                $afficherCompteurLot = ((int) $emprunt->nombre_materiels) > 1;

                $etat = $emprunt->date_reelle_restitution ? $emprunt->etat_restitution : 'En cours';
                $etat_class = '';

                if ($etat === 'OK') {
                    $etat_class = 'w3-text-green';
                } elseif ($etat === 'Endommagé') {
                    $etat_class = 'w3-text-red';
                } elseif ($etat === 'En réparation') {
                    $etat_class = 'w3-text-orange';
                } elseif ($etat === 'En cours') {
                    $etat_class = 'w3-text-blue';
                }
                ?>

                <tr class="item-emprunt" style="<?= $row_style ?>">
                    <td><?= sanitize($emprunt->nom_emprunteur) ?></td>
                    <td><?= sanitize($emprunt->prenom_emprunteur) ?></td>
                    <td><?= sanitize($emprunt->nom_groupe) ?></td>
                    <td><?= formatDisplayDate(sanitize($emprunt->date_emprunt)) ?></td>
                    <td>
                        <?php if ($emprunt->caution === 'Déposée'): ?>
                            <span style="color: green; font-weight: bold;">✓</span>
                        <?php elseif ($emprunt->caution === 'En attente'): ?>
                            <span style="color: red; font-weight: bold;">✕</span>
                        <?php else: ?>
                            <span style="font-weight: bold;">✕</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($afficherCompteurLot): ?>
                            <div>
                                <b><?= (int) $emprunt->nombre_materiels_rendus ?> / <?= (int) $emprunt->nombre_materiels ?></b>
                            </div>
                        <?php endif; ?>
                        <div><?= sanitize($emprunt->materiels_resume) ?></div>
                    </td>
                    <td><?= formatDisplayDate(sanitize($emprunt->date_prevue_restitution)) ?></td>
                    <td style="<?= $is_late ? 'color: red; font-weight: bold;' : '' ?>">
                        <?= formatDisplayDate(sanitize($emprunt->date_reelle_restitution)) ?>
                    </td>
                    <td class="<?= $etat_class ?>" style="font-weight: bold;">
                        <?= sanitize($etat) ?>
                    </td>
                    <td>
                        <?php if ($emprunt->date_reelle_restitution): ?>
                            <?= html_entity_decode(($emprunt->remarque_restitution ?? ''), ENT_QUOTES, 'UTF-8') ?>
                        <?php else: ?>
                            <?= html_entity_decode(($emprunt->remarque ?? ''), ENT_QUOTES, 'UTF-8') ?>
                        <?php endif; ?>
                    </td>
                    <td>
                        <form action="?element=emprunts&action=card" method="post">
                            <input type="hidden" name="id_emprunt" value="<?= $emprunt->id_emprunt ?>">
                            <button type="submit" name="submit" class="w3-button w3-small w3-border w3-round">✏️</button>
                        </form>

                        <?php if (!$emprunt->date_reelle_restitution): ?>
                            <button command="show-modal" commandfor="confirmation-emprunt-dialog-<?= $emprunt->id_emprunt ?>" class="w3-button w3-small w3-border w3-round">📥</button>
                        <?php endif; ?>

                        <dialog class="w3-round-large" id="confirmation-emprunt-dialog-<?= $emprunt->id_emprunt ?>">
                            <div style="min-width: 700px; max-width: 900px;">
                                <h3>Rendu du lot</h3>
                                <p>
                                    Emprunt de <b><?= sanitize($emprunt->nom_emprunteur) ?> <?= sanitize($emprunt->prenom_emprunteur) ?></b>
                                    pour <b><?= sanitize($emprunt->nom_groupe) ?></b>.
                                </p>
                                <p>
                                    <?php if ($afficherCompteurLot): ?>
                                        <b id="compteur-modal-<?= $emprunt->id_emprunt ?>"><?= (int) $emprunt->nombre_materiels_rendus ?> / <?= (int) $emprunt->nombre_materiels ?></b> matériel(s) rendu(s)
                                    <?php else: ?>
                                        <b>Lot unique</b>
                                    <?php endif; ?>
                                </p>

                                <table class="w3-table w3-bordered w3-small w3-margin-bottom">
                                    <thead>
                                        <tr class="w3-blue">
                                            <th>Matériel</th>
                                            <th>État actuel</th>
                                            <th>Rendu</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if ($materielsLot): ?>
                                            <?php foreach ($materielsLot as $materiel): ?>
                                                <tr>
                                                    <td>
                                                        <b><?= sanitize($materiel['nom_materiel']) ?></b><br>
                                                        <?= sanitize($materiel['modele_materiel']) ?><br>
                                                        <?= sanitize($materiel['etiquette_ulco_materiel']) ?>
                                                    </td>
                                                    <td>
                                                        <?php if (!empty($materiel['date_reelle_restitution'])): ?>
                                                            <span class="w3-text-green"><b>Déjà rendu</b></span><br>
                                                            <?= sanitize($materiel['etat_restitution']) ?>
                                                        <?php else: ?>
                                                            <span class="w3-text-blue">En cours</span><br>
                                                            <?php
                                                            if ($materiel['etat_materiel'] === 'OK') {
                                                                echo '<span class="w3-text-green">OK</span>';
                                                            } elseif ($materiel['etat_materiel'] === 'Endommagé') {
                                                                echo '<span class="w3-text-red">Endommagé</span>';
                                                            } elseif ($materiel['etat_materiel'] === 'En réparation') {
                                                                echo '<span class="w3-text-orange">En réparation</span>';
                                                            } elseif ($materiel['etat_materiel'] === 'En cours') {
                                                                echo '<span class="w3-text-blue">En cours</span>';
                                                            }
                                                            ?>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if (empty($materiel['date_reelle_restitution'])): ?>
                                                            <form action="?element=emprunts&action=card" method="post" class="w3-container w3-padding-0 form-rendre-ajax">
                                                                <input type="hidden" name="id_emprunt" value="<?= $emprunt->id_emprunt ?>">
                                                                <input type="hidden" name="id_materiel" value="<?= $materiel['id_materiel'] ?>">
                                                                <select class="w3-select w3-border w3-round w3-margin-bottom" name="etat_restitution" required>
                                                                    <?php foreach (Materiel::$etats as $etatMateriel): ?>
                                                                        <option value="<?= sanitize($etatMateriel) ?>" <?= $etatMateriel === 'OK' ? 'selected' : '' ?>>
                                                                            <?= sanitize($etatMateriel) ?>
                                                                        </option>
                                                                    <?php endforeach; ?>
                                                                </select>
                                                                <input class="w3-input w3-border w3-round w3-margin-bottom" type="text" name="remarque_restitution" placeholder="Remarque de restitution">
                                                                <input type="hidden" name="return_material" value="1">
                                                                <button type="submit" class="w3-button w3-small w3-blue w3-round">Rendre</button>
                                                            </form>
                                                        <?php else: ?>
                                                            <span class="w3-text-green">Terminé</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="3">Aucun matériel associé à cet emprunt.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>

                                <div class="dialog-buttons">
                                    <button commandfor="confirmation-emprunt-dialog-<?= $emprunt->id_emprunt ?>" command="close" class="w3-button w3-small w3-border w3-round">Fermer</button>
                                </div>
                            </div>
                        </dialog>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="11">Aucun emprunt trouvé.</td>
            </tr>
        <?php endif ?>
    </tbody>
</table>

<h2 id="compteurEmprunts"><?= $totalEmprunts ?> / <?= $totalEmprunts ?> emprunt(s) trouvé(s)</h2>

<script>
    function filtrerEmprunts() {
        let input = document.getElementById('searchBarEmprunt').value.toLowerCase();
        let rows = document.getElementsByClassName('item-emprunt');
        let compteurEmprunts = document.getElementById('compteurEmprunts');
        let actionColumns = 1;
        let motsCles = input.split(/[,;]/).map(mot => mot.trim()).filter(Boolean);
        let empruntsVisibles = 0;

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
                empruntsVisibles++;
                continue;
            }

            // On vérifie si les mots clés sont dans le texte des colonnes utiles
            let correspondAtousLesMots = motsCles.every(mot => texteUtileLigne.indexOf(mot) > -1);

            if (correspondAtousLesMots) {
                rows[i].style.display = "";
                empruntsVisibles++;
            } else {
                rows[i].style.display = "none";
            }
        }

        if (compteurEmprunts) {
            compteurEmprunts.textContent = empruntsVisibles + ' / <?= $totalEmprunts ?> emprunt(s) trouvé(s)';
        }
    }

    filtrerEmprunts();

    document.addEventListener("DOMContentLoaded", function() {
        let forms = document.querySelectorAll('.form-rendre-ajax');

        forms.forEach(function(form) {
            form.addEventListener('submit', function(event) {
                event.preventDefault();

                let formData = new FormData(form);
                let idEmprunt = formData.get('id_emprunt'); // on récupère l'ID de l'emprunt

                let tdAction = form.closest('td');
                let tdEtatActuel = tdAction.previousElementSibling;
                let selectEtat = form.querySelector('select[name="etat_restitution"]');
                let etatSelectionne = selectEtat.value;

                let dialog = document.getElementById('confirmation-emprunt-dialog-' + idEmprunt);
                let rowPrincipale = dialog ? dialog.closest('tr.item-emprunt') : null;

                fetch('emprunts/controllers/card.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error("Réponse serveur incorrecte");
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            // mise à jour du Dialog
                            tdEtatActuel.innerHTML = '<span class="w3-text-green"><b>Déjà rendu</b></span><br>' + etatSelectionne;
                            tdAction.innerHTML = '<span class="w3-text-green">Terminé</span>';

                            // mise à jour de la ligne du tableau
                            if (rowPrincipale) {
                                let cellules = rowPrincipale.cells;

                                // colonne Matériel (Compteur + Résumé)
                                let divCompteur = cellules[5].querySelector('b');
                                if (divCompteur) {
                                    divCompteur.textContent = data.nombre_materiels_rendus + ' / ' + data.nombre_materiels;
                                }

                                let divCompteurModal = document.getElementById('compteur-modal-' + idEmprunt);
                                if (divCompteurModal) {
                                    divCompteurModal.textContent = data.nombre_materiels_rendus + ' / ' + data.nombre_materiels;
                                }

                                // colonne Date réelle de restitution
                                if (data.date_reelle_restitution) {
                                    cellules[7].textContent = data.date_reelle_restitution;
                                    cellules[7].style.color = "";
                                    cellules[7].style.fontWeight = "normal";
                                }

                                // colonne État global
                                let cellEtatGlobal = cellules[8];
                                cellEtatGlobal.textContent = data.etat_global;

                                // réapplication des classes w3.css
                                cellEtatGlobal.classList.remove('w3-text-blue', 'w3-text-green', 'w3-text-red', 'w3-text-orange');

                                if (data.etat_global === 'OK') {
                                    cellEtatGlobal.classList.add('w3-text-green');
                                    rowPrincipale.style.opacity = "0.5";
                                } else if (data.etat_global === 'Endommagé') {
                                    cellEtatGlobal.classList.add('w3-text-red');
                                    rowPrincipale.style.opacity = "0.5";
                                } else if (data.etat_global === 'En réparation') {
                                    cellEtatGlobal.classList.add('w3-text-orange');
                                    rowPrincipale.style.opacity = "0.5";
                                } else {
                                    cellEtatGlobal.classList.add('w3-text-blue');
                                    rowPrincipale.style.opacity = "1";
                                }

                                // colonne Remarque de restitution
                                if (data.remarque_restitution) {
                                    cellules[9].textContent = data.remarque_restitution;
                                }
                            }

                        } else {
                            alert("Erreur: " + (data.error || "Inconnue"));
                        }
                    })
                    .catch(error => {
                        console.error("Erreur AJAX :", error);
                        alert("Impossible de joindre le serveur ou erreur de traitement.");
                    });
            });
        });
    });
</script>